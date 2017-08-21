<?php

namespace Jigoshop\Core\Upgrade;

use Jigoshop\Container;
use WPAL\Wordpress;

/**
 * Class AddPositionToAttributesOptions
 *
 * @package Jigoshop\Core\Upgrade;
 */
class AddPositionToAttributesOptions implements Upgrader
{
    /**
     * @param Wordpress $wp
     * @param Container $di

     */
    public function up(Wordpress $wp, Container $di)
    {
        $wpdb = $wp->getWPDB();
        $wpdb->query("ALTER TABLE `{$wpdb->prefix}jigoshop_attribute_option` ADD `position` INT NOT NULL AFTER `attribute_id`;");
    }

    /**
     * @param Wordpress $wp
     * @param Container $di
     */
    public function down(Wordpress $wp, Container $di)
    {
        $wpdb = $wp->getWPDB();
        $wpdb->query("ALTER TABLE `{$wpdb->prefix}jigoshop_attribute_option` DROP `position`");
    }
}
