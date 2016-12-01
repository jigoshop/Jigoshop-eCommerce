<?php

namespace Jigoshop\Api\Routes\V1;

use Jigoshop\Core\Types;
use Jigoshop\Entity\Customer\Guest;
use Jigoshop\Entity\Order as OrderEntity;
use Jigoshop\Exception;
use Jigoshop\Service\OrderService;
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
        /** @var OrderService $service */
        $service = $this->app->getContainer()->di->get('jigoshop.service.order');
        $queryParams = $request->getParams();
        $queryParams['pagelen'] = isset($queryParams['pagelen']) && is_numeric($queryParams['pagelen']) ? (int)$queryParams['pagelen'] : 10;
        $queryParams['page'] = isset($queryParams['page']) && is_numeric($queryParams['page']) ? (int)$queryParams['page'] : 1;
        $allOrders = 12;//$service->getOrdersCount();

        $orders = $service->findByQuery(new \WP_Query([
            'post_type' => Types::ORDER,
            'posts_per_page' => $queryParams['pagelen'],
            'paged' => $queryParams['page'],
            'post_status' => [
                OrderEntity\Status::CANCELLED,
                OrderEntity\Status::COMPLETED,
                OrderEntity\Status::PROCESSING,
                OrderEntity\Status::PENDING,
                OrderEntity\Status::REFUNDED,
                OrderEntity\Status::ON_HOLD
            ]
        ]));

        return $response->withJson([
            'success' => true,
            'all_results' => $allOrders,
            'pagelen' => $queryParams['pagelen'],
            'page' => $queryParams['page'],
            'next' => '',
            'previous' => '',
            'data' => array_values($orders),
        ]);

    }

    public function getOrder(Request $request, Response $response, $args)
    {
        if(!isset($args['id']) || empty($args['id'])) {
            throw new Exception(__('Product ID was not provided', 'jigoshop'), 404);
        }
        /** @var OrderService $service */
        $service = $this->app->getContainer()->di->get('jigoshop.service.order');
        $order = $service->find($args['id']);

        if(!$order instanceof OrderEntity) {
            throw new Exception(__('Product not found.', 'jigoshop'), 404);
        }

        return $response->withJson([
            'success' => true,
            'data' => $order,
        ]);
    }
}