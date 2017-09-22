<?php

namespace Jigoshop\Api\Routes\V1;

use Jigoshop\Api\Contracts\ApiControllerContract;
use Jigoshop\Api\Permission;
use Jigoshop\Core\Types;
use Jigoshop\Entity\Order as OrderEntity;
use Jigoshop\Entity\OrderInterface;
use Jigoshop\Exception;
use Jigoshop\Factory\Order;
use Jigoshop\Helper\Api;
use Jigoshop\Service\CustomerService;
use Jigoshop\Service\OrderService;
use Slim\App;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Class Orders
 * @package Jigoshop\Api\Controller\V1;
 * @author Krzysztof Kasowski
 */
class Orders extends PostController implements ApiControllerContract
{
    /** @var  App */
    protected $app;

    /**
     * @apiDefine OrderReturnObject
     * @apiSuccess {Number}    data.id    The ID.
     * @apiSuccess {Number}    data.number    Ordering number.
     * @apiSuccess {Object}    data.created_at Order creation time.
     * @apiSuccess {Timestamp}    data.created_at.timestamp Create timestamp.
     * @apiSuccess {Datetime}    data.created_at.format Create time in format Y-M-D H:i:s.
     * @apiSuccess {Object}    data.updated_at Order update time.
     * @apiSuccess {Timestamp}    data.updated_at.timestamp Update timestamp.
     * @apiSuccess {Datetime}    data.updated_at.format Update time in format Y-M-D H:i:s.
     * @apiSuccess {Object}    data.completed_at Order completing time. If order was not completed false is returned.
     * @apiSuccess {Timestamp}    data.completed_at.timestamp Completion timestamp.
     * @apiSuccess {Object[]}    data.items Array of ordered items.
     * @apiSuccess {Number}    data.items.id Item id.
     * @apiSuccess {String}    data.items.key Item key.
     * @apiSuccess {String}    data.items.name Item name.
     * @apiSuccess {String}    data.items.type Item type.
     * @apiSuccess {String}    data.items.quantity Item quantity.
     * @apiSuccess {String}    data.items.price Item price.
     * @apiSuccess {Number}    data.items.tax Item tax.
     * @apiSuccess {Array}    data.items.tax_classes Tax classes set for this item.
     * @apiSuccess {Number}    data.items.product Product id.
     * @apiSuccess {Array}    data.items.meta Meta keys for item.
     * @apiSuccess {Bool}    data.price_includes_tax True if price includes tax.
     * @apiSuccess {Object}    data.customer Customer of this order.
     * @apiSuccess {Number}    data.customer.id Customer id.
     * @apiSuccess {String}    data.customer.login Customer login.
     * @apiSuccess {String}    data.customer.email Customer email.
     * @apiSuccess {String}    data.customer.name Customer name.
     * @apiSuccess {Object} data.customer.billing Customer's billing data.
     * @apiSuccess {String} data.customer.billing.company Customer's company name.
     * @apiSuccess {String} data.customer.billing.euvatno  Customer's billing vat number.
     * @apiSuccess {Object} data.customer.billing.parent  Customer's billing data.
     * @apiSuccess {String} data.customer.billing.parent.first_name  Customer's billing first name.
     * @apiSuccess {String} data.customer.billing.parent.last_name  Customer's billing last name.
     * @apiSuccess {String} data.customer.billing.parent.address Customer's billing address.
     * @apiSuccess {String} data.customer.billing.parent.city Customer's billing city.
     * @apiSuccess {String} data.customer.billing.parent.postcode Customer's billing postcode.
     * @apiSuccess {String} data.customer.billing.parent.country Customer's billing country code.
     * @apiSuccess {String} data.customer.billing.parent.state Customer's billing state.
     * @apiSuccess {String} data.customer.billing.parent.email Customer's billing email.
     * @apiSuccess {String} data.customer.billing.parent.phone Customer's billing phone.
     * @apiSuccess {Object} data.customer.shipping Customer's shipping data.
     * @apiSuccess {String} data.customer.shipping.first_name Customer's shipping first name.
     * @apiSuccess {String} data.customer.shipping.last_name Customer's shipping last name.
     * @apiSuccess {String} data.customer.shipping.address Customer's shipping address.
     * @apiSuccess {String} data.customer.shipping.city Customer's shipping city.
     * @apiSuccess {String} data.customer.shipping.postcode Customer's shipping postcode.
     * @apiSuccess {String} data.customer.shipping.country Customer's shipping country code.
     * @apiSuccess {String} data.customer.shipping.state Customer's shipping state.
     * @apiSuccess {String} data.customer.shipping.email Customer's shipping email.
     * @apiSuccess {String} data.customer.shipping.phone Customer's shipping phone.
     * @apiSuccess {String} data.customer.taxAddres Customer's s address type chosen for tax.
     * @apiSuccess {Object} data.shipping Shipping data set for this order.
     * @apiSuccess {String} data.shipping.method Shipping method.
     * @apiSuccess {Number} data.shipping.price Shipping price.
     * @apiSuccess {String} data.shipping.rate Shipping rate.
     * @apiSuccess {String} data.payment Payment.
     * @apiSuccess {String} data.customer_note Additional info provided by customer.
     * @apiSuccess {Number} data.total Total price.
     * @apiSuccess {Object} data.tax Tax for this order.
     * @apiSuccess {Number} data.tax.standard Standard tax.
     * @apiSuccess {Object} data.shipping_tax Shipping tax for this order.
     * @apiSuccess {Number} data.shipping_tax.standard Standard tax.
     * @apiSuccess {Float} data.subtotal Subtotal.
     * @apiSuccess {Float} data.discount Discount calculated for this order.
     * @apiSuccess {Array} data.coupons Array of coupons ids used for this order.
     * @apiSuccess {String} data.status Current status of order.
     * @apiSuccess {Array} data.update_messages Additional messages added when order is updated.
     */

    /**
     * @apiDefine OrderData
     * @apiParam {Array}  jigoshop_order .
     * @apiParam {Number}  jigoshop_order.customer Customer id.
     * @apiParam {Array[]} [jigoshop_order.items] Array of ordered items.
     * @apiParam {Number}    [jigoshop_order.items.quantity] Item key.
     * @apiParam {Number} [jigoshop_order.items.product] Product id.
     * @apiParam {Bool}  [jigoshop_order.price_includes_tax] True if price includes tax.
     * @apiParam {Object} [jigoshop_order.biling_address] Customer's billing data.
     * @apiParam {String} [jigoshop_order.biling_address.company] Customer's company name.
     * @apiParam {String} [jigoshop_order.biling_address.euvatno]  Customer's billing vat number.
     * @apiParam {String} [jigoshop_order.biling_address.first_name]  Customer's billing first name.
     * @apiParam {String} [jigoshop_order.biling_address.last_name]  Customer's billing last name.
     * @apiParam {String} [jigoshop_order.biling_address.address] Customer's billing address.
     * @apiParam {String} [jigoshop_order.biling_address.city] Customer's billing city.
     * @apiParam {String} [jigoshop_order.biling_address.postcode] Customer's billing postcode.
     * @apiParam {String} [jigoshop_order.biling_address.country] Customer's billing country code.
     * @apiParam {String} [jigoshop_order.biling_address.state] Customer's billing state.
     * @apiParam {String} [jigoshop_order.biling_address.email] Customer's billing email.
     * @apiParam {String} [jigoshop_order.biling_address.phone] Customer's billing phone.
     * @apiParam {Object} [jigoshop_order.shipping_address] Customer's shipping data.
     * @apiParam {String} [jigoshop_order.shipping_address.first_name] Customer's shipping first name.
     * @apiParam {String} [jigoshop_order.shipping_address.last_name] Customer's shipping last name.
     * @apiParam {String} [jigoshop_order.shipping_address.address] Customer's shipping address.
     * @apiParam {String} [jigoshop_order.shipping_address.city] Customer's shipping city.
     * @apiParam {String} [jigoshop_order.shipping_address.postcode] Customer's shipping postcode.
     * @apiParam {String} [jigoshop_order.shipping_address.country] Customer's shipping country code.
     * @apiParam {String} [jigoshop_order.shipping_address.state] Customer's shipping state.
     * @apiParam {String} [jigoshop_order.shipping_address.email] Customer's shipping email.
     * @apiParam {String} [jigoshop_order.shipping_address.phone] Customer's shipping phone.
     * @apiParam {Bool} [jigoshop_order.completed_at] If true, then current completed time will be set.
     * @apiParam {String} [jigoshop_order.payment] Payment.
     * @apiParam {String} [jigoshop_order.customer_note] Additional info provided by customer.
     * @apiParam {Array} [jigoshop_order.coupons] Array of coupons ids used for this order.
     * @apiParam {String='pending','on-hold','processing','completed','cancelled','refunded'} [jigoshop_order.status='pending'] Current status of order.
     * @apiParam {String} [order] Additional order data.
     * @apiParam {Number} [order.shipping] Shipping method.
     * @apiParam {Float} [order.discount] Discount calculated for this order.
     */

    /**
     * Orders constructor.
     * @param App $app
     */
    public function __construct(App $app)
    {
        parent::__construct($app);

        $this->app = $app;

        /**
         * @api {get} /orders Get Orders
         * @apiName FindOrders
         * @apiGroup Order
         *
         * @apiUse findAllReturnData
         * @apiSuccess {Object[]} data Array of orders objects.
         * @apiUse OrderReturnObject
         * @apiPermission read_orders
         */
        $app->get('', [$this, 'getOrders']);

        /**
         * @api {get} /orders/:id Get Order information
         * @apiName GetOrders
         * @apiGroup Order
         *
         * @apiParam (Url Params) {Number} id Order unique ID.
         *
         * @apiSuccess {Object} data Order object.
         * @apiUse OrderReturnObject
         *
         * @apiUse validateObjectFindingError
         * @apiPermission manage_orders
         */
        $app->get('/{id:[0-9]+}', [$this, 'getOrder']);

        /**
         * @api {post} /orders Create a Order
         * @apiName PostOrder
         * @apiGroup Order
         *
         * @apiUse OrderData
         *
         * @apiUse StandardSuccessResponse
         * @apiPermission manage_orders
         */
        $app->post('', [$this, 'create']);

        /**
         * @api {put} /orders/:id Update a Order
         * @apiName PutOrder
         * @apiGroup Order
         *
         * @apiParam (Url Params) {Number} id Order unique ID.
         * @apiUse OrderData
         *
         * @apiUse StandardSuccessResponse
         * @apiUse validateObjectFindingError
         * @apiPermission manage_orders
         */
        $app->put('/{id:[0-9]+}', [$this, 'update']);

        /**
         * @api {delete} /orders/:id Delete a Order
         * @apiName DeleteOrder
         * @apiGroup Order
         *
         * @apiParam (Url Params) {Number} id Order unique ID.
         *
         * @apiUse StandardSuccessResponse
         * @apiUse validateObjectFindingError
         * @apiPermission manage_orders
         */
        $app->delete('/{id:[0-9]+}', [$this, 'delete']);
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

        $query = new \WP_Query([
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
        ]);
        $orders = $service->findByQuery($query);

        return $response->withJson([
            'success' => true,
            'all_results' => $query->found_posts,
            'pagelen' => $queryParams['pagelen'],
            'page' => $queryParams['page'],
            'next' => Api::getNextPagePath('/orders', $queryParams['page'], $queryParams['pagelen'], $query->found_posts),
            'previous' => Api::getPreviousPagePath('/orders', $queryParams['page'], $queryParams['pagelen'], $query->found_posts),
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
            throw new Exception(__('Order ID was not provided', 'jigoshop-ecommerce'), 404);
        }
        /** @var OrderService $service */
        $service = $this->app->getContainer()->di->get('jigoshop.service.order');
        $order = $service->find($args['id']);

        if (!$order instanceof OrderEntity) {
            throw new Exception(__('Order not found.', 'jigoshop-ecommerce'), 404);
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
        $postData = $request->getParsedBody();
        if(!isset($postData['jigoshop_order']) || !is_array($postData['jigoshop_order'])) {
            throw new Exception('Invalid parameters', 422);
        }

        $postId = $this->createNewPostOrder();
        /** @var Order $factory */
        $factory = $this->app->getContainer()->di->get("jigoshop.factory.order");
        $object = $factory->create($postId);

        if (isset($postData['jigoshop_order']['customer'])) {
            /** @var CustomerService $customerService */
            $customerService = $this->app->getContainer()->di->get("jigoshop.service.customer");
            $postData['jigoshop_order']['customer'] = $customerService->find($postData['jigoshop_order']['customer']);
        }
        if (isset($postData['jigoshop_order']['items'])) {
            $object = $this->_updateOrderItems($object, $postData['jigoshop_order']['items']);
        }
        $object = $factory->fill($object, $postData['jigoshop_order']);
        /** @var OrderService $service */
        $this->service->save($object);

        return $response->withJson([
            'success' => true,
            'data' => $object,
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
            throw new Exception("$this->entityName ID was not provided", 422);
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
        /** @var Order $factory */
        $factory = $this->app->getContainer()->di->get("jigoshop.factory.$this->entityName");
        $object = $factory->fill($object, $putData['jigoshop_order']);

        $service = $this->app->getContainer()->di->get("jigoshop.service.$this->entityName");
        $service->save($object);

        return $response->withJson([
            'success' => true,
            'data' => $object,
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
            throw new Exception("$this->entityName ID was not provided", 422);
        }

        $object = $this->service->find($args['id']);
        if (!$object instanceof OrderEntity) {
            throw new Exception("Order not found.", 404);
        }
        if (isset($request->getParsedBody()['jigoshop_order']['items'])) {
            $this->_updateOrderItems($object, $request->getParsedBody()['jigoshop_order']['items']);
        }

        return $response->withJson([
            'success' => true,
            'data' => $object,
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
                throw new Exception(__('Product not found.', 'jigoshop-ecommerce'));
            }

            /** @var OrderEntity\Item $item */
            $item = $wp->applyFilters('jigoshop\cart\add', null, $product);

            if ($item === null) {
                throw new Exception(__('Product cannot be added to the order.', 'jigoshop-ecommerce'));
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

        $wpdb->insert($wpdb->posts, [
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
        ]);

        $id = $wpdb->insert_id;
        if (!is_int($id) || $id === 0) {
            throw new Exception(__('Unable to save order. Please try again.', 'jigoshop-ecommerce'));
        }
        return $id;
    }
}