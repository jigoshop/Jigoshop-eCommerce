<?php

namespace Jigoshop\Api\Routes\V1\Order;

use Jigoshop\Admin\Migration\Exception;
use Jigoshop\Admin\Page\Product;
use Jigoshop\Api\Contracts\ApiControllerContract;
use Jigoshop\Api\Routes\V1\BaseController;
use Jigoshop\Entity\Order as OrderEntity;
use Jigoshop\Entity\Product\Attribute as AttributeEntity;
use Jigoshop\Service\OrderService;
use Slim\App;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Class Items
 * @package Jigoshop\Api\Routes\V1;
 * @author MAciej Maciaszek
 */
class Items extends BaseController implements ApiControllerContract
{
    /** @var  App */
    protected $app;
    /** @var OrderEntity $order */
    protected $order;

    /**
     * order service is service we are using for order items
     * @var string
     */
    protected $serviceName = 'jigoshop.service.order';
    /**
     * item entity is Product
     * @var string
     */
    protected $entityName = 'item';

    /**
     * @apiDefine OrderItemReturnObject
     * @apiSuccess {Number}     items.id Item id.
     * @apiSuccess {String}     items.key Item key.
     * @apiSuccess {String}     items.name Item name.
     * @apiSuccess {String}     items.type Item type.
     * @apiSuccess {String}     items.quantity Item quantity.
     * @apiSuccess {String}     items.price Item price.
     * @apiSuccess {Number}     items.tax Item tax.
     * @apiSuccess {Array}     items.tax_classes Tax classes set for this item.
     * @apiSuccess {Number}     items.product Product id.
     * @apiSuccess {Array}     items.meta Meta keys for item.
     */
    /**
     * @apiDefine OrderItemData
     * @apiParam {Number} [items.price] Item key.
     * @apiParam {Number} [items.product] Product id.
     */
    /**
     * Products constructor.
     * @param App $app
     */
    public function __construct(App $app)
    {
        parent::__construct($app);
        $this->app = $app;

        /**
         * @api {get} /orders/:id/items Get Order Items
         * @apiName FindOrderItems
         * @apiGroup OrderItem
         *
         * @apiParam (Url Params) {Number} id Order id
         * @apiUse findAllReturnData
         *
         * @apiSuccess {Object[]} data Array of order items objects.
         * @apiUse OrderItemReturnObject
         * @apiPermission read_orders
         */
        $app->get('', [$this, 'findAll']);

        /**
         * @api {get} /orders/:id/items/:itemId Get Order Item information
         * @apiName GetOrderItems
         * @apiGroup OrderItem
         *
         * @apiParam (Url Params) {Number} id Order unique ID.
         * @apiParam (Url Params) {Number} id Item unique ID.
         *
         * @apiSuccess {Object} data Order item object.
         * @apiUse OrderItemReturnObject
         *
         * @apiError UnprocessableEntity Attribute Id or Order Id was not provided.
         * @apiError ObjectNotFound Order have not been found or it does not have this item.
         * @apiPermission read_orders
         */
        $app->get('/{id:[0-9]+}', [$this, 'findOne']);

        /**
         * @api {post} /orders/:id/items Add item to an order
         * @apiName PostOrderItem
         * @apiGroup OrderItem
         *
         * @apiParam (Url Params) {Number} id Order unique ID.
         * @apiUse OrderItemData
         *
         * @apiUse StandardSuccessResponse
         *
         * @apiError UnprocessableEntity Order Id was not provided.
         * @apiError ObjectNotFound Order have not been found.
         * @apiPermission manage_orders
         */
        $app->post('/{id:[0-9]+}', [$this, 'create']);

        /**
         * @api {put} /orders/:id/items/:itemId Update a Item in the Order
         * @apiName PutOrderItem
         * @apiGroup OrderItem
         *
         * @apiParam (Url Params) {Number} id Order unique ID.
         * @apiParam (Url Params) {Number} itemId Item unique ID.
         * @apiUse OrderItemData
         *
         * @apiUse StandardSuccessResponse
         *
         * @apiError UnprocessableEntity Attribute Id or Order Id was not provided.
         * @apiError ObjectNotFound Order have not been found or it does not have this item.
         * @apiPermission manage_orders
         */
        $app->put('/{id:[0-9]+}', [$this, 'update']);

        /**
         * @api {delete} /orders/:id/items/:itemId Delete Item from an order
         * @apiName DeleteOrderItem
         * @apiGroup OrderItem
         *
         * @apiParam (Url Params) {Number} id Order unique ID.
         * @apiParam (Url Params) {Number} itemId Item unique ID.
         *
         * @apiUse StandardSuccessResponse
         *
         * @apiError UnprocessableEntity Attribute Id or Order Id was not provided.
         * @apiError ObjectNotFound Order have not been found or it does not have this item.
         * @apiPermission manage_orders
         */
        $app->delete('/{id:[0-9]+}', [$this, 'delete']);
    }

    /**
     * get all items for order
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return Response
     */
    public function findAll(Request $request, Response $response, $args)
    {
        $queryParams = $this->setDefaultQueryParams($request->getParams());

        $this->setOrder($args);
        $items = $this->getObjects($args);
        $itemsCount = $this->getObjectsCount();
        return $response->withJson([
            'success' => true,
            'all_results' => $itemsCount,
            'pagelen' => $queryParams['pagelen'],
            'page' => $queryParams['page'],
            'next' => '',
            'previous' => '',
            'data' => array_values($items),
        ]);
    }

    /**
     * get specified item from order
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return Response
     */
    public function findOne(Request $request, Response $response, $args)
    {
        $this->setOrder($args);
        $id = $args['id'];
        $result = array_filter(
            $this->order->getItems(),
            function ($e) use ($id) {
                return $e->getProductId() == $id;
            });
        if(empty($result)){
            throw new Exception("Order doesn't have this item", 404);
        }
        reset($result);
        $item = (current($result));
//        $product = $this->validateObjectFinding($args);
//        $item = new OrderEntity\Item();
//        $item->setProduct($product);
//        $key = $this->app->getContainer()->di->get('jigoshop.service.product')->generateItemKey($item);
//        if (!$this->order->hasItem($key)) {
//            throw new Exception("Order doesn't have this item", 404);
//        }
        return $response->withJson([
            'success' => true,
            'data' => $item,
        ]);
    }

    /**
     * overrided create function from BaseController
     * adding item to order
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return Response
     */
    public function create(Request $request, Response $response, $args)
    {
        $item = $this->createOrUpdateOrderItems($args, $_POST);

        return $response->withJson([
            'success' => true,
            'data' => $item,
        ]);
    }

    /**
     * overrided update function from BaseController
     * updating item in order
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return Response
     */
    public function update(Request $request, Response $response, $args)
    {
        $item = $this->createOrUpdateOrderItems($args, $request->getParsedBody());

        return $response->withJson([
            'success' => true,
            'data' => $item,
        ]);
    }

    /**
     * remove item from order
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return Response
     */
    public function delete(Request $request, Response $response, $args)
    {
        $this->setOrder($args);
        $id = $args['id'];
        $result = array_filter(
            $this->order->getItems(),
            function ($e) use ($id) {
                return $e->getProductId() == $id;
            });
        if(empty($result)){
            throw new Exception("Order doesn't have this item", 404);
        }
        reset($result);
        $item = (current($result));
        $this->order->removeItem($item->getKey());
        $this->service->save($this->order);
        return $response->withJson([
            'success' => true,
            'data' => $this->order,
        ]);
    }

    /**
     * setting order
     * @param $args
     */
    protected function setOrder($args)
    {
        // validating product first
        if (!isset($args['orderId']) || empty($args['orderId'])) {
            throw new Exception("Order Id was not provided", 422);
        }
        $order = $this->service->find($args['orderId']);
        if (!$order instanceof OrderEntity) {
            throw new Exception("Order not found.", 404);
        }
        $this->order = $order;
    }

    /**
     * saving Order entity
     */
    protected function saveOrder()
    {
        /** @var OrderService $service */
        $service = $this->app->getContainer()->di->get("jigoshop.service.order");
        $service->save($this->order);
    }

    /**
     * @param array $args
     * @return OrderEntity\Item[]
     */
    protected function getObjects(array $args)
    {
        return $this->order->getItems();
    }

    /**
     * @return int
     */
    protected function getObjectsCount()
    {
        return count($this->order->getItems());
    }

    /**
     * find product and validate it
     * @param $args
     * @return mixed
     */
    protected function validateObjectFinding($args)
    {
        if (!isset($args['id']) || empty($args['id'])) {
            throw new Exception("Item ID was not provided");
        }

        $object = $this->app->getContainer()->di->get('jigoshop.service.product')->find($args['id']);
        $entity = self::JIGOSHOP_ENTITY_PREFIX . 'Product';

        if (!$object instanceof $entity) {
            throw new Exception("Product not found.", 404);
        }

        return $object;
    }

    /**
     * creating or updating order single item so that items could be filled in order
     * @param $args
     * @param $data
     *
     * @return OrderEntity\Item
     */
    private function createOrUpdateOrderItems($args, $data)
    {
        if (!isset($data['item'])) {
            throw new Exception('No item data was correctly provided.');
        }
        $this->setOrder($args);

        /** @var Product $product */
        $product = $this->validateObjectFinding($args);
        /** @var \Jigoshop\Factory\Order $factory */
        $factory = $this->app->getContainer()->di->get('jigoshop.factory.order');
        $item = $factory->updateOrderItemByProductId($this->order, $product->getId(), $data['item']);

        $this->saveOrder();

        return $item;
    }

}