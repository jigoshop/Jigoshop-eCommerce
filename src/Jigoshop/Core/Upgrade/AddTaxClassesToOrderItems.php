<?php

namespace Jigoshop\Core\Upgrade;

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
     */
    public function up(Wordpress $wp)
    {
        $wpdb = $wp->getWPDB();
        $wpdb->query("ALTER TABLE `{$wpdb->prefix}jigoshop_order_item` ADD `tax_classes` VARCHAR(170) NOT NULL AFTER `title`;");
    }

    /**
     * @param Wordpress $wp
     */
    public function down(Wordpress $wp)
    {
        $wpdb = $wp->getWPDB();
        $wpdb->query("ALTER TABLE `{$wpdb->prefix}jigoshop_order_item` DROP `tax_classes`");
    }
}