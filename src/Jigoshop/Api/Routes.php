<?php

namespace Jigoshop\Api;

use Firebase\JWT\JWT;
use Jigoshop\Core\Options;
use Jigoshop\Exception;
use Slim\App;
use Slim\Http\Request;
use Slim\Http\Response;
use Tuupola\Base62;

/**
 * Class Routes
 * @package Jigoshop\Api;
 * @author Krzysztof Kasowski
 */
class Routes
{
    /** @var  Options */
    private $options;

    /**
     * Routes constructor.
     * @param Options $options
     */
    public function __construct(Options $options)
    {
        $this->options = $options;
    }

    /**
     * @param App $app
     * @param string $version
     */
    public function init(App $app, $version)
    {
        /**
         * @api {get} /ping verify connection to API.
         * @apiName Ping
         * @apiGroup Main
         *
         * @apiSuccess {Bool} success Request status .
         * @apiSuccess {String} time Response time.
         */
        $app->get('/ping', [$this, 'ping']);
        if ($version == 1) {
            /**
             * @api {post} /token setting token variable for authorization header
             * @apiName Token
             * @apiGroup Main
             *
             * @apiSuccess {Bool} success Request status .
             * @apiSuccess {String} token Token that is needed to include in authorization header.
             * @apiError UserNotFound User not found.
             */
            $app->post('/token', [$this, 'token']);
            $app->group('/attributes', function () use ($app) {
                new Routes\V1\Attributes($app);
            });
            $app->group('/coupons', function () use ($app) {
                new Routes\V1\Coupons($app);
            });
            $app->group('/customers', function () use ($app) {
                new Routes\V1\Customers($app);
            });
            $app->group('/emails', function () use ($app) {
                new Routes\V1\Emails($app);
            });
            $app->group('/orders', function () use ($app) {
                new Routes\V1\Orders($app);
                $app->group('/{orderId:[0-9]+}/items', function () use ($app) {
                    new Routes\V1\Order\Items($app);
                });
            });
            $app->group('/products', function () use ($app) {
                new Routes\V1\Products($app);
                $app->group('/{productId:[0-9]+}/attributes', function () use ($app) {
                    new Routes\V1\Product\Attributes($app);
                });
            });
            $app->group('/reports', function () use ($app) {
                new Routes\V1\Reports($app);
            });
        }
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return Response
     */
    public function ping(Request $request, Response $response, $args)
    {
        return $response->withJson([
            'success' => true,
            'time' => time(),
        ]);
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param $args
     *
     * @return Response
     */
    public function token(Request $request, Response $response, $args)
    {

        $now = new \DateTime();
        $future = new \DateTime('now +2 hours');
        $server = $request->getServerParams();
        $jti = Base62::encode(random_bytes(16));

        $sub = '';
        $permissions = [];
        $users = $this->options->get('advanced.api.users', []);
        foreach ($users as $user) {
            if ($user['login'] == $server['PHP_AUTH_USER']) {
                $sub = $user['login'];
                $permissions = $user['permissions'];
            }
        }

        if ($sub == '') {
            throw new Exception('User not found.', 401);
        }

        $payload = [
            'iat' => $now->getTimestamp(),
            'exp' => $future->getTimestamp(),
            'jti' => $jti,
            'sub' => $sub,
            'permissions' => $permissions
        ];

        $token = JWT::encode($payload, $this->options->get('advanced.api.secret', ''), 'HS256');

        return $response->withJson([
            'success' => true,
            'token' => $token,
        ]);
    }
}