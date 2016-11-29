<?php

namespace Jigoshop\Api\Routes\V1;

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
    /** @var  App */
    private $app;

    /**
     * Orders constructor.
     * @param App $app
     */
    public function __construct(App $app)
    {
        $this->app = $app;
        $app->get('', array($this, 'getOrders'));
        $app->get('/{id:[0-9]+}', array($this, 'getOrder'));
    }

    public function getOrders(Request $request, Response $response, $args)
    {
        $service = $this->app->getContainer()->di->get('jigoshop.service.order');
    }

    public function getOrder(Request $request, Response $response, $args)
    {
        $service = $this->app->getContainer()->di->get('jigoshop.service.order');
    }
}