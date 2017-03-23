<?php

namespace Jigoshop\Core\Upgrade;
use Jigoshop\Container;
use Jigoshop\Entity\Coupon;
use Jigoshop\Entity\Order;
use Jigoshop\Service\CouponService;
use Jigoshop\Service\OrderService;
use WPAL\Wordpress;

/**
 * Class ConvertAllDiscounts
 * @package Jigoshop\Core\Upgrade;
 * @author Krzysztof Kasowski
 */
class ConvertAllDiscounts implements Upgrader
{
    /** @var CouponService $couponService */
    private $couponService;
    /** @var OrderService $orderService */
    private $orderService;
    /**
     * @param Wordpress $wp
     * @param Container $di
     */
    public function up(Wordpress $wp, Container $di)
    {
        $wpdb = $wp->getWpdb();
        $this->couponService = $di->get('jigoshop.service.coupon');
        $this->orderService = $di->get('jigoshop.service.order');

        $results = $wpdb->get_results("
          SELECT meta1.post_id as id, meta1.meta_value as discount, meta2.meta_value as coupons FROM {$wpdb->postmeta} as meta1
          LEFT JOIN {$wpdb->postmeta} as meta2 on (meta2.post_id = meta1.post_id AND meta2.meta_key = %s)
          WHERE meta1.meta_key = %s AND (meta1.meta_value + 0.0) > 0", ['discount', 'coupons']);

        foreach($results as $result) {
            $coupons = array_values(maybe_unserialize($result['coupons']));
            if(count($coupons)) {
                $discouts = [];
                if(is_string($coupons[0])) {
                    $discouts = $this->convertCouponsFromJSE($result, $coupons);
                } elseif (is_array($coupons[0])) {
                    $discouts = $this->convertCouponsFromJSX($result, $coupons);
                }
                if(count($discouts)) {
                    $this->saveDiscounts($wpdb, $discouts);
                }
            }
        }
    }

    /**
     * @param Wordpress $wp
     * @param Container $di
     */
    public function down(Wordpress $wp, Container $di)
    {

    }

    private function convertCouponsFromJSE($result, $coupons)
    {
        $removed = [];
        /** @var Order\Discount[] $discounts */
        $discounts = [];
        foreach ($coupons as $code) {
            $coupon = $this->couponService->findByCode($code);
            if($coupon instanceof Coupon) {
                $order = $this->orderService->find($result['id']);
                $discount = $coupon->getDiscount($order);
                $discounts[] = $discount;
                $result['discount'] -= $discount->getAmount();
            }
        }
        if($result['discount'] < 0) {
            foreach($discounts as $discount) {
                $discount->setAmount($discount->getAmount() - abs($result['discount']) / count($discounts));
            }
        }
        foreach($removed as $code) {
            $discount = new Order\Discount();
            $discount->setType(Order\Discount\Type::COUPON);
            $discount->setCode($code);
            $discount->setAmount($result['discount'] / count($removed));
            $discounts[] = $discount;
        }

        return $discounts;
    }

    private function convertCouponsFromJSX($result, $coupons)
    {
//            'id'                    => false,
//            'code'                  => false,
//            'type'                  => true,
//            'amount'                => true,
//            'date_from'             => true,
//            'date_to'               => true,
//            'usage_limit'           => true,
//            'usage'                 => true,
//            'free_shipping'         => true,
//            'individual_use'        => true,
//            'order_total_min'       => true,
//            'order_total_max'       => true,
//            'include_products'      => true,
//            'exclude_products'      => true,
//            'include_categories'    => true,
//            'exclude_categories'    => true,
//            'pay_methods'           => true,

        /** @var Order\Discount[] $discounts */
        $discounts = [];
        foreach($coupons as $coupon) {
            $couponEntity = new Coupon();
            //$couponEntity->set
        }
    }

    /**
     * @param \wpdb $wpdb
     * @param Order\Discount[] $discounts
     */
    private function saveDiscounts($wpdb, $discounts)
    {
        foreach ($discounts as $discount) {
            $wpdb->insert($wpdb->prefix . 'jigoshop_order_discount', [
                'type' => $discount->getType(),
                'code' => $discount->getCode(),
                'amount' => $discount->getAmount(),
            ]);
            $id = $wpdb->insert_id;
            foreach ($discount->getAllMeta() as $meta) {
                $wpdb->insert($wpdb->prefix . 'jigoshop_order_discount_meta', [
                    'discount_id' => $id,
                    'meta_key' => $meta->getKey(),
                    'meta_value' => $meta->getValue(),
                ]);
            }
        }
    }
}