<?php

namespace Jigoshop\Core\Upgrade;

use Jigoshop\Container;
use WPAL\Wordpress;

/**
 * Class AddTaxClassesToOrderItems
 *
 * @package Jigoshop\Core\Upgrade;
 * @author Krzysztof Kasowski
 */
class AddTaxClassesToOrderItems implements Upgrader
{
    /**
     * @param Wordpress $wp
     * @param Container $di
     */
    public function up(Wordpress $wp, Container $di)
    {
        $wpdb = $wp->getWPDB();
        $wpdb->query("ALTER TABLE `{$wpdb->prefix}jigoshop_order_item` ADD `tax_classes` VARCHAR(170) NOT NULL AFTER `title`;");
    }

    /**
     * @param Wordpress $wp
     * @param Container $di
     */
    public function down(Wordpress $wp, Container $di)
    {
        $wpdb = $wp->getWPDB();
        $wpdb->query("ALTER TABLE `{$wpdb->prefix}jigoshop_order_item` DROP `tax_classes`");
    }
}