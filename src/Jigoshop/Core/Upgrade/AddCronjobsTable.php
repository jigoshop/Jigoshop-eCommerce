<?php
namespace Jigoshop\Core\Upgrade;

use Jigoshop\Container;
use WPAL\Wordpress;

class AddCronjobsTable implements Upgrader {
    /**
     * @param Wordpress $wp
     * @param Container $di

     */
    public function up(Wordpress $wp, Container $di) {
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
            CREATE TABLE IF NOT EXISTS {$wpdb->prefix}jigoshop_cronjobs (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `jobKey` varchar(128) NOT NULL,
              `executeAt` int(11) NOT NULL,
              `executeEvery` int(11) NOT NULL,
              `lastExecutedAt` int(11) NOT NULL,
              `callback` text NOT NULL,
              PRIMARY KEY id (id)
            ) {$collate};        
        ";        

        $wpdb->query($query);
    }

    /**
     * @param Wordpress $wp
     * @param Container $di
     */
    public function down(Wordpress $wp, Container $di) {
        $wpdb = $wp->getWPDB();
        $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}jigoshop_cronjobs");
    }	
}