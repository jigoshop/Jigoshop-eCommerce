<?php

namespace Jigoshop\Api\Validation;
/**
 * Class Permissions
 * @author Krzysztof Kasowski
 */
class Permission
{
    const READ_PRODUCTS = 'read_orders';
    const READ_CART = 'read_cart';
    const READ_ORDERS = 'read_orders';
    const READ_COUPONS = 'read_coupons';
    const READ_CUSTOMERS = 'read_customers';
    const MANAGE_PRODUCTS = 'manage_products';
    const MANAGE_CART = 'manage_cart';
    const MANAGE_ORDERS = 'manage_orders';
    const MANAGE_COUPONS = 'manage_coupons';
    const MANAGE_CUSTOMERS = 'manage_customers';
    const MANAGE_EMAILS = 'manage_emails';

    private static $permissions;

    /**
     * @return array
     */
    public static function getPermisions()
    {
        if(self::$permissions == null) {
            self::$permissions = apply_filters('jigoshop\api\validation\permission\get_permissions', array(
                self::READ_PRODUCTS => __('Read products', 'jigoshop'),
                self::READ_CART => __('Read cart', 'jigoshop'),
                self::READ_ORDERS => __('Read orders', 'jigoshop'),
                self::READ_COUPONS => __('Read coupons', 'jigoshop'),
                self::READ_CUSTOMERS => __('Read customers', 'jigoshop'),
                self::MANAGE_PRODUCTS => __('Manage products', 'jigoshop'),
                self::MANAGE_CART => __('Manage cart', 'jigoshop'),
                self::MANAGE_ORDERS => __('Manage orders', 'jigoshop'),
                self::MANAGE_COUPONS => __('Manage coupons', 'jigoshop'),
                self::MANAGE_CUSTOMERS => __('Manage customers', 'jigoshop'),
                self::MANAGE_EMAILS => __('Manage emails', 'jigoshop'),
            ));
        }

        return self::$permissions;
    }

    /**
     * Checks if selected status exists.
     *
     * @param $permission string Status name.
     *
     * @return bool Does status exists?
     */
    public static function exists($permission)
    {
        $permissions = self::getPermisions();

        return isset($permissions[$permission]);
    }

    /**
     * Checks if selected status exists.
     *
     * @param $permission string Status name.
     *
     * @return bool Does status exists?
     */
    public static function getName($permission)
    {
        if (!self::exists($permission)) {
            return $permission;
        }

        $permissions = self::getPermisions();

        return $permissions[$permission];
    }
}