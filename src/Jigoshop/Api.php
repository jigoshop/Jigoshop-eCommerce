<?php

namespace Jigoshop;

use Firebase\JWT\JWT;
use Jigoshop\Admin\Dashboard;
use Jigoshop\Api\Routes;
use Jigoshop\Core\Options;
use Jigoshop\Middleware\ApiPermissionMiddleware;
use Monolog\Logger;
use Monolog\Registry;
use Slim;
use Slim\App;
use Slim\Container as SlimContainer;
use Slim\Http\Environment;
use Tuupola\Base62;
use WPAL\Wordpress;

/**
 * Class Api
 * @author Krzysztof Kasowski
 */
class Api
{
    const QUERY_URI = 'jigoshop_rest_uri';
    const QUERY_VERSION = 'jigoshop_rest_version';
    const URL_PATTERN = 'api/v([0-9])([0-9a-zA-Z\-_/]+)';

    /** @var Wordpress */
    private $wp;
    /** @var Options */
    private $options;
    /** @var Container */
    private $di;

    /**
     * Api constructor.
     * @param Wordpress $wp
     * @param Options $options
     * @param Container $di
     */
    public function __construct(Wordpress $wp, Options $options, Container $di)
    {
        $this->wp = $wp;
        $this->options = $options;
        $this->di = $di;
    }

    public function run()
    {
        $this->wp->addFilter('query_vars', [$this, 'addQueryVars'], 0);
        $this->wp->addAction('init', [$this, 'addRewrite'], 1);
        $this->wp->addAction('parse_request', [$this, 'parseRequest'], 0);
    }

    /**
     * Adds Jigoshop API query var to available vars.
     *
     * @param $vars array Current list of variables.
     *
     * @return array Updated list of variables.
     */
    public function addQueryVars($vars)
    {
        $vars[] = self::QUERY_URI;
        $vars[] = self::QUERY_VERSION;

        return $vars;
    }

    /**
     * Adds rewrite endpoint for processing Jigoshop APIs
     */
    public function addRewrite()
    {
        $this->wp->addRewriteRule(
            $this->wp->getRewrite()->root . self::URL_PATTERN . '?$',
            sprintf('index.php?%s=$matches[1]&%s=/$matches[2]', self::QUERY_VERSION, self::QUERY_URI),
            'top'
        );
    }

    /**
     * @param \WP_Query $query
     */
    public function parseRequest($query)
    {
        $version = isset($query->query_vars[self::QUERY_VERSION]) ? $query->query_vars[self::QUERY_VERSION] : null;
        $uri = isset($query->query_vars[self::QUERY_URI]) ? str_replace('//', '/',
            $query->query_vars[self::QUERY_URI]) : null;

        if ($version && $uri) {
            $app = new App($this->getSlimContainer($uri));
            $this->addMiddlewares($app);
            $this->addRoutes($app, $version);

            $app->run();
            exit;
        }
    }

    /**
     * @param $uri
     *
     * @return SlimContainer
     */
    private function getSlimContainer($uri)
    {
        $di = $this->di;
        $container = new SlimContainer([
            'environment' => function () use ($uri) {
                $server = $_SERVER;
                $server['REQUEST_URI'] = $uri;
                return new Environment($server);
            },
            'di' => function () use ($di) {
                return $di;
            },
            'token' => function () {
                return new Api\Token();
            }
        ]);

        return $container;
    }

    /**
     * @param App $app
     */
    private function addMiddlewares(App $app)
    {
        $container = $app->getContainer();
        $users = [];
        foreach ($this->options->get('advanced.api.users', []) as $user) {
            $users[$user['login']] = $user['password'];
        }
        $secret = $this->options->get('advanced.api.secret', '');
        if (empty($users) || empty($secret)) {
            throw new Exception('Users or secret key was not set up.', 500);
        }

        $app->add(new Slim\Middleware\HttpBasicAuthentication([
            'path' => '/token',
            'relaxed' => ['localhost', 'jigoshop2.dev'],
            'secure' => false,
            'users' => $users
        ]));

        $app->add(new ApiPermissionMiddleware($app, [
            'path' => '/',
            'passthrough' => ['/token', '/ping'],
            'relaxed' => ['localhost', 'jigoshop2.dev'],
        ]));

        $app->add(new Slim\Middleware\JwtAuthentication([
            'path' => '/',
            'passthrough' => ['/token', '/ping'],
            'secret' => $secret,
            'secure' => false,
            'relaxed' => ['localhost', 'jigoshop2.dev'],
            'logger' => Registry::getInstance(\JigoshopInit::getLogger()),
            'callback' => function ($request, $response, $args) use ($container) {
                $container->token->restoreState($args['decoded']);
            }
        ]));

    }

    /**
     * @param App $app
     */
    private function addRoutes(App $app, $version)
    {
        $this->initDefaultHandlers($app);
        (new Routes($this->options))->init($app, $version);

        array_map(function (Extension $extension) use ($app, $version) {
            $extension->getApi()->init($app, $version);
        }, Extensions::getExtensions());
    }

    /**
     * @param App $app
     */
    private function initDefaultHandlers(App $app)
    {
        $container = $app->getContainer();
        $container['notFoundHandler'] = function ($container) {
            return function () use ($container) {
                return $container['response']->withStatus(404)->withJson([
                    'success' => false,
                    'error' => __('Resource not found', 'jigoshop-ecommerce')
                ]);
            };
        };
        $container['errorHandler'] = function ($container) {
            return function ($request, $response, $exception) use ($container) {
                return $container['response']->withStatus($exception->getCode())->withJson([
                    'success' => false,
                    'error' => $exception->getMessage()
                ]);
            };
        };
    }
}