<?php

namespace Jigoshop\Core\Upgrade;

use Jigoshop\Container;
use Monolog\Registry;
use WPAL\Wordpress;

/**
 * Class CreateDiscountsTables
 * @package Jigoshop\Core\Upgrade;
 * @author Krzysztof Kasowski
 */
class CreateDiscountsTables implements Upgrader
{

    /**
     * @param Wordpress $wp
     * @param Container $di
     */
    public function up(Wordpress $wp, Container $di)
    {
        $wpdb = $wp->getWPDB();
        $collate = '';
        if ($wpdb->has_cap('collation')) {
            if (!empty($wpdb->charset)) {
                $collate = "DEFAULT CHARACTER SET {$wpdb->charset}";
            }
            if (!empty($wpdb->collate)) {
                $collate .= " COLLATE {$wpdb->collate}";
            }
        }

        $query = "
			CREATE TABLE IF NOT EXISTS {$wpdb->prefix}jigoshop_order_discount (
				id INT NOT NULL AUTO_INCREMENT,
				order_id BIGINT(20) UNSIGNED,
				type VARCHAR(255) NOT NULL,
			    code VARCHAR(255) NOT NULL,
				amount DECIMAL(12,4) NOT NULL,
				PRIMARY KEY id (id),
				FOREIGN KEY discount_order (order_id) REFERENCES {$wpdb->posts} (ID) ON DELETE CASCADE
			) {$collate};
		";
        if (!$wpdb->query($query)) {
            Registry::getInstance(\JigoshopInit::getLogger())->addCritical(sprintf('Unable to create table "%s". Error: "%s".', 'jigoshop_order_item', $wpdb->last_error));
        }
        $query = "
			CREATE TABLE IF NOT EXISTS {$wpdb->prefix}jigoshop_order_discount_meta (
				discount_id INT,
				meta_key VARCHAR(170) NOT NULL,
				meta_value TEXT NOT NULL,
				PRIMARY KEY id (discount_id, meta_key),
				FOREIGN KEY order_discount (discount_id) REFERENCES {$wpdb->prefix}jigoshop_order_discount (id) ON DELETE CASCADE
			) {$collate};
		";
        if (!$wpdb->query($query)) {
            Registry::getInstance(JIGOSHOP_LOGGER)->addCritical(sprintf('Unable to create table "%s". Error: "%s".', 'jigoshop_order_item_meta', $wpdb->last_error));
        }
    }

    /**
     * @param Wordpress $wp
     * @param Container $di
     */
    public function down(Wordpress $wp, Container $di)
    {
        $wpdb = $wp->getWPDB();
        $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}jigoshop_order_discount_meta");
        $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}jigoshop_order_discount");
    }
}