<?php

namespace Jigoshop\Core\Upgrade;

use Jigoshop\Container;
use WPAL\Wordpress;

/**
 * Class ReplaceAttachmentTypes
 * @package Jigoshop\Core\Upgrade;
 * @author Krzysztof Kasowski
 */
class ReplaceAttachmentTypes implements Upgrader
{

    /**
     * @param Wordpress $wp
     * @param Container $di
     */
    public function up(Wordpress $wp, Container $di)
    {
        $wpdb = $wp->getWPDB();
        $wpdb->update($wpdb->prefix.'jigoshop_product_attachment', [
            'type' => 'image',
        ], [
            'type' => 'gallery'
        ]);
        $wpdb->update($wpdb->prefix.'jigoshop_product_attachment', [
            'type' => 'datafile',
        ], [
            'type' => 'downloads'
        ]);
    }

    /**
     * @param Wordpress $wp
     * @param Container $di
     */
    public function down(Wordpress $wp, Container $di)
    {
        $wpdb = $wp->getWPDB();
        $wpdb->update($wpdb->prefix.'jigoshop_product_attachment', [
            'type' => 'gallery',
        ], [
            'type' => 'image'
        ]);
        $wpdb->update($wpdb->prefix.'jigoshop_product_attachment', [
            'type' => 'downloads',
        ], [
            'type' => 'datafile'
        ]);
    }
}