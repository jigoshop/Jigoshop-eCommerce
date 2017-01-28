<?php

namespace Jigoshop\Core\Upgrade;

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
     */
    public function up(Wordpress $wp)
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
     */
    public function down(Wordpress $wp)
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