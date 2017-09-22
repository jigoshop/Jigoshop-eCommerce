<?php

namespace Jigoshop\Entity\Order\Discount;
use WPAL\Wordpress;

/**
 * Class Types
 * @package Jigoshop\Entity\Order\Discount;
 * @author Krzysztof Kasowski
 */
class Type
{
    const COUPON = 'coupon';
    const USER_DEFINED = 'user_defined';

    /** @var  Wordpress */
    private static $wp;
    /** @var  array */
    private static $types;

    /**
     * @param Wordpress $wp
     */
    public static function setWordpress($wp)
    {
        self::$wp = $wp;
    }

    /**
     * Checks if selected type exists.
     *
     * @param $type string Type name.
     *
     * @return bool Does type exists?
     */
    public static function exists($type)
    {
        $types = self::getTypes();

        return isset($types[$type]);
    }

    /**
     * @return array List of available order discount types.
     */
    public static function getTypes()
    {
        if (self::$types === null) {
            self::$types = self::$wp->applyFilters('jigoshop\order\discount\types', [
                Type::COUPON => __('Coupon', 'jigoshop-ecommerce'),
                Type::USER_DEFINED => __('User Defined', 'jigoshop-ecommerce'),
            ]);
        }

        return self::$types;
    }

    /**
     * Returns Type name.
     *
     * If name is not found - returns given identifier.
     *
     * @param $type string Type identifier.
     *
     * @return string Type name.
     */
    public static function getName($type)
    {
        if (!self::exists($type)) {
            return $type;
        }

        $types = self::getTypes();

        return $types[$type];
    }
}