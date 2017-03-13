<?php

namespace Jigoshop\Api\Routes\V1;

use Jigoshop\Api\Contracts\ApiControllerContract;
use Slim\App;

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
     * @apiSuccess {String}    data.free_shipping Is free shipping.
     * @apiSuccess {Number}    data.order_total_minimum Required minimum subtotal for this coupon to be valid on an order.
     * @apiSuccess {Number}    data.order_total_maximum Required maximum subtotal for this coupon to be valid on an order.
     * @apiSuccess {Array}    data.products Products that are available to use coupon for.
     * @apiSuccess {Array}    data.excluded_products Products that are not available to use coupon for.
     * @apiSuccess {Array}    data.categories Categories that coupon is available for.
     * @apiSuccess {Array}    data.excluded_categories Categories that are excluded from usage of this coupon.
     * @apiSuccess {Array}    data.payment_methods Payment methods.
     */
    /**
     * @apiDefine CouponData
     * @apiParam {Array} jigoshop_coupon Jigoshop coupon array of data.
     * @apiParam {Timestamp} [jigoshop_coupon.from] Time from when counpon's available to use.
     * @apiParam {Timestamp} [jigoshop_coupon.to] Time to when counpon's available to use.
     * @apiParam {Number} [jigoshop_coupon.usage_limit] Limit of coupon usages.
     * @apiParam {String} [jigoshop_coupon.type] Coupon type.
     * @apiParam {Number} [jigoshop_coupon.order_total_minimum] Required minimum subtotal for this coupon to be valid on an order.
     * @apiParam {Number} [jigoshop_coupon.order_total_maximum] Required minimum subtotal for this coupon to be valid on an order.
     * @apiParam {String='on','off'} [jigoshop_coupon.individual_use='off'] Individual usage.
     * @apiParam {String='on','off'} [jigoshop_coupon.freeShipping='off'] Show the Free Shipping method on the checkout with this enabled.
     * @apiParam {String} [jigoshop_coupon.amount] Coupon's value.
     * @apiParam {Array} [jigoshop_coupon.products] Products that are available to use coupon for.
     * @apiParam {Array} [jigoshop_coupon.excluded_products] Products that are not available to use coupon for.
     * @apiParam {Array} [jigoshop_coupon.categories] Categories that are available to use coupon for.
     * @apiParam {Array} [jigoshop_coupon.excluded_categories] Categories that are not available to use coupon for.
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
         * @api {get} /coupon/ Request Coupons
         * @apiName FindCoupons
         * @apiGroup Coupon
         *
         * @apiUse findAllReturnData
         * @apiSuccess {Object[]} data List of coupons.
         * @apiUse CouponReturnObject
         */
        $app->get('', array($this, 'findAll'));

        /**
         * @api {get} /coupon/:id Request Coupon information
         * @apiName GetCoupon
         * @apiGroup Coupon
         *
         * @apiParam {Number} id Coupon unique ID.
         *
         * @apiUse CouponReturnObject
         *
         * @apiUse validateObjectFindingError
         */
        $app->get('/{id:[0-9]+}', array($this, 'findOne'));

        /**
         * @api {post} /coupon Create a Coupon
         * @apiName PostCoupon
         * @apiGroup Coupon
         *
         * @apiUse CouponData
         *
         * @apiSuccess {Bool} success Response status.
         * @apiSuccess {String} data Response information.
         */
        $app->post('', array($this, 'create'));

        /**
         * @api {put} /coupon/ Update a Coupon
         * @apiName PutCoupon
         * @apiGroup Coupon
         *
         * @apiParam {Number} id Coupon unique ID.
         * @apiUse CouponData
         *
         * @apiSuccess {Bool} success Response status.
         * @apiSuccess {String} data Response information.
         * @apiUse validateObjectFindingError
         */
        $app->put('/{id:[0-9]+}', array($this, 'update'));

        /**
         * @api {delete} /coupon/:id Delete a Coupon
         * @apiName DeleteCoupon
         * @apiGroup Coupon
         *
         * @apiParam {Number} id Coupon unique ID.
         *
         * @apiSuccess {Bool} success Response status.
         * @apiSuccess {String} data Response information.
         * @apiUse validateObjectFindingError
         */
        $app->delete('/{id:[0-9]+}', array($this, 'delete'));
    }


}