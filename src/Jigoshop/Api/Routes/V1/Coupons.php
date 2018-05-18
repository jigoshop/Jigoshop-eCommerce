<?php

namespace Jigoshop\Api\Routes\V1;

use Jigoshop\Api\Contracts\ApiControllerContract;
use Slim\App;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Class Coupons
 * @package Jigoshop\Api\Routes\V1;
 * @author Maciej Maciaszek
 */
class Coupons extends PostController implements ApiControllerContract
{
    /** @var  App */
    protected $app;

    /**
     * @apiDefine CouponReturnObject
     * @apiSuccess {Number}    data.id    The ID.
     * @apiSuccess {String}    data.type  Type of coupon.
     * @apiSuccess {String}    data.title Title.
     * @apiSuccess {String}    data.amount Coupon value.
     * @apiSuccess {Number}    data.from Time from when counpon's available to use.
     * @apiSuccess {Number}    data.to Time to when counpon's available to use.
     * @apiSuccess {Number}    data.usage_limit Limit of coupon usages.
     * @apiSuccess {Number}    data.free_shipping This value is set to 1 if coupon provides free shipping.
     * @apiSuccess {Number}    data.order_total_minimum Required minimum subtotal for this coupon to be valid on an order.
     * @apiSuccess {Number}    data.order_total_maximum Required maximum subtotal for this coupon to be valid on an order.
     * @apiSuccess {Array}    data.products Products that coupon can apply to.
     * @apiSuccess {Array}    data.excluded_products Products this coupon cannot be applied to.
     * @apiSuccess {Array}    data.categories Categories which this coupon can apply to. If this is left blank it will have effect on all of the products.
     * @apiSuccess {Array}    data.excluded_categories Categories that this coupon cannot be applied to..
     * @apiSuccess {Array}    data.payment_methods Payment methods that are allowed for this coupon to be effective.
     */
    /**
     * @apiDefine CouponData
     * @apiParam {Array} [post_title] Coupon name.
     * @apiParam {Array} [jigoshop_coupon] Jigoshop coupon array of data.
     * @apiParam {Datetime} [jigoshop_coupon.from] Time from when counpon's available to use in format Y-M-D h:i.s.
     * @apiParam {Datetime} [jigoshop_coupon.to] Time to when counpon's available to use in format Y-M-D h:i.s.
     * @apiParam {Number} [jigoshop_coupon.usage_limit] Limit of coupon usages.
     * @apiParam {String='fixed_cart', 'percent_cart', 'fixed_product', 'percent_product'} [jigoshop_coupon.type='fixed_cart'] Coupon type.
     * @apiParam {Number} [jigoshop_coupon.order_total_minimum] Required minimum subtotal for this coupon to be valid on an order.
     * @apiParam {Number} [jigoshop_coupon.order_total_maximum] Required minimum subtotal for this coupon to be valid on an order.
     * @apiParam {String='on','off'} [jigoshop_coupon.individual_use='off'] Individual usage.
     * @apiParam {String='on','off'} [jigoshop_coupon.freeShipping='off'] Show the Free Shipping method on the checkout with this enabled.
     * @apiParam {String} [jigoshop_coupon.amount] Coupon's value.
     * @apiParam {Array} [jigoshop_coupon.products] Which products this coupon can apply to. If this is left blank it will have effect on all of the products.
     * @apiParam {Array} [jigoshop_coupon.excluded_products] Which products this coupon cannot be applied to.
     * @apiParam {Array} [jigoshop_coupon.categories] Which categories this coupon can apply to. If this is left blank it will have effect on all of the products.
     * @apiParam {Array} [jigoshop_coupon.excluded_categories] Categories that are not available to use coupon for.
     * @apiParam {Array} [jigoshop_coupon.payment_methods] Which payment methods are allowed for this coupon to be effective.
     */

    /**
     * Coupons constructor.
     * @param App $app
     */
    public function __construct(App $app)
    {
        parent::__construct($app);
        $this->app = $app;

        /**
         * @api {get} /coupon/ Get Coupons
         * @apiName FindCoupons
         * @apiGroup Coupon
         *
         * @apiUse findAllReturnData
         * @apiSuccess {Object[]} data Array of coupons objects.
         * @apiUse CouponReturnObject
         * @apiPermission read_coupons
         */
        $app->get('', [$this, 'findAll']);

        /**
         * @api {get} /coupons/:id Get Coupon information
         * @apiName GetCoupon
         * @apiGroup Coupon
         *
         * @apiParam (Url Params) {Number} id Coupon unique ID.
         *
         * @apiSuccess {Object} data Coupon object.
         * @apiUse CouponReturnObject
         *
         * @apiUse validateObjectFindingError
         * @apiPermission read_coupons
         */
        $app->get('/{id:[0-9]+}', [$this, 'findOne']);

        /**
         * @api {post} /coupons Create a Coupon
         * @apiName PostCoupon
         * @apiGroup Coupon
         *
         * @apiUse CouponData
         *
         * @apiSuccess {Bool} success Response status.
         * @apiSuccess {String} data Response information.
         * @apiPermission manage_coupons
         */
        $app->post('', [$this, 'create']);

        /**
         * @api {put} /coupons/:id Update a Coupon
         * @apiName PutCoupon
         * @apiGroup Coupon
         *
         * @apiParam (Url Params) {Number} id Coupon unique ID.
         * @apiUse CouponData
         *
         * @apiSuccess {Bool} success Response status.
         * @apiSuccess {String} data Response information.
         * @apiUse validateObjectFindingError
         * @apiPermission manage_coupons
         */
        $app->put('/{id:[0-9]+}', [$this, 'update']);

        /**
         * @api {delete} /coupons/:id Delete a Coupon
         * @apiName DeleteCoupon
         * @apiGroup Coupon
         *
         * @apiParam (Url Params) {Number} id Coupon unique ID.
         *
         * @apiSuccess {Bool} success Response status.
         * @apiSuccess {String} data Response information.
         * @apiUse validateObjectFindingError
         * @apiPermission manage_coupons
         */
        $app->delete('/{id:[0-9]+}', [$this, 'delete']);

    }

    /**
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return Response
     */
    public function create(Request $request, Response $response, $args)
    {
        if(isset($_POST['jigoshop_coupon']['title'])) {
            $_POST['post_title'] = $_POST['jigoshop_coupon']['title'];
        }

        return parent::create($request, $response, $args);
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return Response
     */
    public function update(Request $request, Response $response, $args)
    {
        $object = $this->validateObjectFinding($args);

        $factory = $this->app->getContainer()->di->get("jigoshop.factory.$this->entityName");
        $data = $request->getParsedBody();
        if(isset($data['jigoshop_coupon']['title'])) {
            $data['post_title'] = $data['jigoshop_coupon']['title'];
        }
        $object = $factory->update($object, $data); //updating object with parsed variables

        $service = $this->app->getContainer()->di->get("jigoshop.service.$this->entityName");
        $service->updateAndSavePost($object);

        return $response->withJson([
            'success' => true,
            'data' => $object,
        ]);
    }
}