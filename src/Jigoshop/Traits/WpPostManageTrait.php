<?php

namespace Jigoshop\Traits;

/**
 * Class WpPostManageTrait
 * @package Jigoshop\Traits
 */
trait WpPostManageTrait
{

    /**
     * inserts post with specified type, returns its id
     * @param $wp (WP instance)
     * @param $object
     * @param $postType
     * @return int
     */
    public function insertPost($wp, $object, $postType)
    {
        $wpdb = $wp->getWPDB();
        $date = $wp->getHelpers()->currentTime('mysql');
        $dateGmt = $wp->getHelpers()->currentTime('mysql', true);
        //assign title from one of post methods to get title or name
        $title = method_exists($object, 'getTitle') ? $object->getTitle() :
            (method_exists($object, 'getName') ? $object->getName() : null);

        $wpdb->insert($wpdb->posts, array(
            'post_author' => 0, //TODO #316 ticket update posts
            'post_date' => $date,
            'post_date_gmt' => $dateGmt,
            'post_modified' => $date,
            'post_modified_gmt' => $dateGmt,
            'post_type' => $postType,
            'post_title' => $title,
            'post_name' => sanitize_title($title),
            'ping_status' => 'closed',
            'comment_status' => 'closed',
        ));

        return $wpdb->insert_id;
    }
}