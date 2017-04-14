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

            //if has own referring permissions
            if (property_exists(self::API_NAMESPACE . '\\' . ucfirst($className), 'referringPermission')) {
                $class = new \ReflectionClass(self::API_NAMESPACE . '\\' . ucfirst($className));
                $property = $class->getDefaultProperties()['referringPermission'];
                $this->checkPermission($this->resolvePermission($request->getMethod()) . '_' . $property);
            } //validate
            else {
                $this->checkPermission($this->resolvePermission($request->getMethod()) . '_' . $className);
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

    /**
     * throw exception when permission not in token
     * @param $permissionName
     */
    private function checkPermission($permissionName)
    {
        if (!$this->app->getContainer()->token->hasPermission($permissionName)) {
            throw new Exception('You have no permissions to access to this page.', 401);
        }
    }

    /**
     * resolve permission from request method
     * @param $requestMethod
     * @return bool|string
     */
    private function resolvePermission($requestMethod)
    {
        switch ($requestMethod) {
            case "GET":
                /* check reading permission */
                return 'read';
            case 'POST':
            case 'PUT':
            case 'DELETE':
                /* check managing permission */
                return 'manage';
            default:
                return false;
        }
    }

}