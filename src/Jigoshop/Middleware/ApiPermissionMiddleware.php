<?php

namespace Jigoshop\Middleware;

use Jigoshop\Exception;
use Psr\Http\Message\RequestInterface;
use Slim\App;

class ApiPermissionMiddleware
{
    /** namespace to api controllers */
    const API_NAMESPACE = 'Jigoshop\Api\Routes\V1';

    /** @var App */
    private $app;
    /** @var array */
    private $options;

    /**
     * ApiPermissionMiddleware constructor.
     * @param App $app
     */
    public function __construct(App $app, array $options = [])
    {
        $this->app = $app;
        $this->options = $options;
    }

    /**
     * Middleware invokable class
     *
     * @param  \Psr\Http\Message\ServerRequestInterface $request PSR7 request
     * @param  \Psr\Http\Message\ResponseInterface $response PSR7 response
     * @param  callable $next Next middleware
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function __invoke($request, $response, $next)
    {
        if (false === $this->shouldAuthenticate($request)) {
            return $next($request, $response);
        }

        $className = explode('/', $request->getUri()->getPath())[1] ?: null;
        if ($className && class_exists(self::API_NAMESPACE . '\\' . ucfirst($className))) {
            /* check reading permission */
            if ($request->isGet()) {
                if (!$this->app->getContainer()->token->hasPermission('read_' . $className)) {
                  throw new Exception('You have no permissions to access to this page.', 401);
                }
            } /* check managing permission */
            elseif ($request->isPost() || $request->isPut() || $request->isDelete()) {
                if (!$this->app->getContainer()->token->hasPermission('manage_' . $className)) {
                    throw new Exception('You have no permissions to access to this page.', 401);
                }
            }
        }

        return $next($request, $response);
    }

    /**
     * Check if middleware should authenticate
     *
     * @param \Psr\Http\Message\RequestInterface $request
     * @return boolean True if middleware should authenticate.
     */
    public function shouldAuthenticate(RequestInterface $request)
    {
        $uri = "/" . $request->getUri()->getPath();
        $uri = str_replace("//", "/", $uri);

        /* If request path is matches passthrough should not authenticate. */
        foreach ((array)$this->options["passthrough"] as $passthrough) {
            $passthrough = rtrim($passthrough, "/");
            if (!!preg_match("@^{$passthrough}(/.*)?$@", $uri)) {
                return false;
            }
        }

        /* Otherwise check if path matches and we should authenticate. */
        foreach ((array)$this->options["path"] as $path) {
            $path = rtrim($path, "/");
            if (!!preg_match("@^{$path}(/.*)?$@", $uri)) {
                return true;
            }
        }
        return false;
    }

}