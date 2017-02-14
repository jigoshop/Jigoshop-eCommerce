<?php

namespace Jigoshop\Core\Upgrade;

use Jigoshop\Container;
use WPAL\Wordpress;

/**
 * Class ChangeUpSellsCrossSellsMetaNames
 * @package Jigoshop\Core\Upgrade;
 * @author Krzysztof Kasowski
 */
class ChangeUpSellsCrossSellsMetaNames implements Upgrader
{

    /**
     * @param Wordpress $wp
     * @param Container $di
     */
    public function up(Wordpress $wp, Container $di)
    {
        $wpdb = $wp->getWPDB();
        $wpdb->update($wpdb->postmeta, [
            'meta_key' => 'cross_sells'
        ], [
            'meta_key' => 'crosssell_ids'
        ]);
        $wpdb->update($wpdb->postmeta, [
            'meta_key' => 'up_sells'
        ], [
            'meta_key' => 'upsell_ids'
        ]);
    }

    /**
     * @param Wordpress $wp
     * @param Container $di
     */
    public function down(Wordpress $wp, Container $di)
    {
        $wpdb = $wp->getWPDB();
        $wpdb->update($wpdb->postmeta, [
            'meta_key' => 'crosssell_ids'
        ], [
            'meta_key' => 'cross_sells'
        ]);
        $wpdb->update($wpdb->postmeta, [
            'meta_key' => 'upsell_ids'
        ], [
            'meta_key' => 'up_sells'
        ]);
    }
}