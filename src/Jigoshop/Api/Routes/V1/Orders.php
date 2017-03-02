<?php

namespace Jigoshop\Api\Routes\V1;

use Jigoshop\Api\Permission;
use Jigoshop\Core\Types;
use Jigoshop\Entity\Customer\Guest;
use Jigoshop\Entity\Order as OrderEntity;
use Jigoshop\Entity\OrderInterface;
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
class Orders extends PostController
{
    /** @var  App */
    protected $app;

    /**
     * Orders constructor.
     * @param App $app
     */
    public function __construct(App $app)
    {
        parent::__construct($app);

        $this->app = $app;
        $app->get('', array($this, 'getOrders'));
        $app->get('/{id:[0-9]+}', array($this, 'getOrder'));
        $app->post('', array($this, 'create'));
        $app->put('/{id:[0-9]+}', array($this, 'update'));
    }

    /**
     * basic method to GET all orders
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return Response
     */
    public function getOrders(Request $request, Response $response, $args)
    {
        if (!$this->app->getContainer()->token->hasPermission(Permission::READ_ORDERS)) {
            throw new Exception('You have no permissions to access to this page.', 403);
        }

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

    /**
     * basic method to GET single order
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return Response
     */
    public function getOrder(Request $request, Response $response, $args)
    {
        if (!$this->app->getContainer()->token->hasPermission(Permission::READ_ORDERS)) {
            throw new Exception('You have no permissions to access to this page.', 403);
        }

        if (!isset($args['id']) || empty($args['id'])) {
            throw new Exception(__('Order ID was not provided', 'jigoshop'), 404);
        }
        /** @var OrderService $service */
        $service = $this->app->getContainer()->di->get('jigoshop.service.order');
        $order = $service->find($args['id']);

        if (!$order instanceof OrderEntity) {
            throw new Exception(__('Order not found.', 'jigoshop'), 404);
        }

        return $response->withJson([
            'success' => true,
            'data' => $order,
        ]);
    }

    /**
     * overriden function of PostController to create order
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return Response
     */
    public function create(Request $request, Response $response, $args)
    {
        $postId = $this->createNewPostOrder();
        $factory = $this->app->getContainer()->di->get("jigoshop.factory.order");
        $object = $factory->create($postId);

        if (isset($_POST['jigoshop_order']['customer'])) {
            $customerService = $this->app->getContainer()->di->get("jigoshop.service.customer");
            $_POST['jigoshop_order']['customer'] = $customerService->find($_POST['jigoshop_order']['customer']);
        }
        if (isset($_POST['jigoshop_order']['items'])) {
            $object = $this->_updateOrderItems($object, $_POST['jigoshop_order']['items']);
        }
        $object = $factory->fill($object, $_POST['jigoshop_order']);
        $service = $this->app->getContainer()->di->get("jigoshop.service.order");
        $service->save($object);

        return $response->withJson([
            'success' => true,
            'data' => "$this->entityName successfully created",
        ]);
    }

    /**
     * overriden function of PostController to update orders
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return Response
     */
    public function update(Request $request, Response $response, $args)
    {
        if (!isset($args['id']) || empty($args['id'])) {
            throw new Exception("$this->entityName ID was not provided");
        }

        $object = $this->service->find($args['id']);
        if (!$object instanceof OrderEntity) {
            throw new Exception("Order not found.", 404);
        }

        $putData = $request->getParsedBody();
        if (isset($putData['jigoshop_order']['customer'])) {
            $putData['jigoshop_order']['customer'] = $object->getCustomer(); //setting customer
        }
        if (isset($putData['jigoshop_order']['items'])) {
            $object = $this->_updateOrderItems($object, $putData['jigoshop_order']['items']);
        }
        $factory = $this->app->getContainer()->di->get("jigoshop.factory.$this->entityName");
        $object = $factory->fill($object, $putData['jigoshop_order']);

        $service = $this->app->getContainer()->di->get("jigoshop.service.$this->entityName");
        $service->save($object);

        return $response->withJson([
            'success' => true,
            'data' => "Order successfully updated",
        ]);
    }

    //TODO and move to items controller
    /**
     * function updating only items in order
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return Response
     */
    public function updateOrderItems(Request $request, Response $response, $args)
    {
        if (!isset($args['id']) || empty($args['id'])) {
            throw new Exception("$this->entityName ID was not provided");
        }

        $object = $this->service->find($args['id']);
        if (!$object instanceof OrderEntity) {
            throw new Exception("Order not found.", 404);
        }
        if (isset($request->getParsedBody()['jigoshop_order']['items'])) {
            $object = $this->_updateOrderItems($object, $request->getParsedBody()['jigoshop_order']['items']);
        }

        return $response->withJson([
            'success' => true,
            'data' => "Order successfully updated",
        ]);
    }

    /**
     * updating order and items array so that items could be filled in order
     * @param OrderInterface $order
     * @param array $itemsData
     * @return OrderInterface
     */
    private function _updateOrderItems(OrderInterface $order, array &$itemsData)
    {
        $productService = $this->app->getContainer()->di->get("jigoshop.service.product");
        $wp = $this->app->getContainer()->di->get("wpal");
        foreach ($itemsData as &$singleItem) {
            $post = $wp->getPost((int)$singleItem['product']);
            if ($post->post_type == 'product_variation' && $post->post_parent > 0) {
                $post = $wp->getPost($post->post_parent);
                //TODO: change this!!!
                $singleItem['variation_id'] = (int)$singleItem['product'];
                $singleItem['quantity'] = 1;
            }

            $product = $productService->findforPost($post);

            if ($product->getId() === null) {
                throw new Exception(__('Product not found.', 'jigoshop'));
            }

            /** @var Item $item */
            $item = $wp->applyFilters('jigoshop\cart\add', null, $product);

            if ($item === null) {
                throw new Exception(__('Product cannot be added to the order.', 'jigoshop'));
            }
            $key = $productService->generateItemKey($item);
            $item->setKey($key);
            $item->setQuantity((int)$singleItem['quantity']);
            if (isset($singleItem['price']) && is_numeric($singleItem['price'])) {
                $item->setPrice((float)$singleItem['price']);
            }
            if ($item->getQuantity() > 0) {
                $item = $wp->applyFilters('jigoshop\admin\order\update_product', $item, $order);
            }
            $order->addItem($item);
            $singleItem = $item;
        }
        return $order;
    }


    /**
     * creates new post of order type that is needed for
     * @return int
     */
    private function createNewPostOrder()
    {
        $wp = $this->app->getContainer()->di->get("wpal");
        $wpdb = $wp->getWPDB();
        $date = $wp->getHelpers()->currentTime('mysql');
        $dateGmt = $wp->getHelpers()->currentTime('mysql', true);

        $wpdb->insert($wpdb->posts, array(
            'post_author' => $_POST['customer_id'] ?: 0, //TODO create function that will receive customer from api
            'post_date' => $date,
            'post_date_gmt' => $dateGmt,
            'post_modified' => $date,
            'post_modified_gmt' => $dateGmt,
            'post_type' => Types::ORDER,
            'post_title' => $_POST['post_title'] ?: '',
            'post_excerpt' => $_POST['customer_note'] ?: '',
            'post_name' => sanitize_title($_POST['post_title'] ?: ''),
            'comment_status' => 'open',
            'ping_status' => 'closed',
        ));

        $id = $wpdb->insert_id;
        if (!is_int($id) || $id === 0) {
            throw new Exception(__('Unable to save order. Please try again.', 'jigoshop'));
        }
        return $id;
    }
}