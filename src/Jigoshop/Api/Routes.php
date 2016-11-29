<?php

namespace Jigoshop\Api;

use Firebase\JWT\JWT;
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

    /**
     * @param App $app
     * @param string $version
     */
    public function init(App $app, $version)
    {
        $app->get('/ping', array($this, 'ping'));
        if($version == 1) {
            $app->post('/token', array($this, 'token'));
            $app->group('/orders', function() use ($app) {
                new Routes\V1\Orders($app);
            });
            $app->group('/products', function() use ($app) {
                new Routes\V1\Products($app);
            });
            $app->group('/reports', function() use ($app) {
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
        $scopes = [];
        $now = new \DateTime();
        $future = new \DateTime('now +2 hours');
        $server = $request->getServerParams();
        $jti = Base62::encode(random_bytes(16));

        $payload = [
            'iat' => $now->getTimestamp(),
            'exp' => $future->getTimestamp(),
            'jti' => $jti,
            'sub' => $server['PHP_AUTH_USER'],
            'scope' => $scopes
        ];

        $token = JWT::encode($payload, 'supersecretkeyyoushouldnotcommittogithub2', 'HS256');

        return $response->withJson([
            'success' => true,
            'token' => $token,
        ]);
    }
}