<?php

namespace Jigoshop\Api\Controller\V1;

use Slim\App;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Class Orders
 * @package Jigoshop\Api\Controller\V1;
 * @author Krzysztof Kasowski
 */
class Orders
{
    /**
     * Orders constructor.
     * @param App $app
     */
    public function __construct(App $app)
    {
        $app->get('', array($this, 'getOrders'));
        $app->get('/:id', array($this, 'getOrder'));
    }

    public function getOrders(Request $request, Response $response, $args)
    {
        var_dump($args);
    }

    public function getOrder(Request $request, Response $response, $args)
    {
        
    }
}