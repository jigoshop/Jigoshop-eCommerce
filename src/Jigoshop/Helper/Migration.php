<?php

namespace Jigoshop\Helper;

class Migration
{
    /** @var  bool  */
    private static $needMigrationTool;

	/**
	 * Checks whether a migration tool must be displayed.
	 * If someone did not use before Jigoshop 1.x does not need migration tools in Jigoshop eCommerce
	 *
	 * @return boolean
	 */
	public static function needMigrationTool()
	{
        global $wpdb;

        if(is_null(self::$needMigrationTool)) {
            self::$needMigrationTool =
                $wpdb->query("SELECT * FROM {$wpdb->options} WHERE option_name = 'jigoshop_db_version' OR option_name = 'jigoshop_options'") &&
                $wpdb->query("SELECT * FROM {$wpdb->term_taxonomy} WHERE taxonomy = 'shop_order_status'") &&
                $wpdb->query("SHOW TABLES LIKE '{$wpdb->prefix}jigoshop_termmeta'");
        }

		return self::$needMigrationTool;
	}
}
