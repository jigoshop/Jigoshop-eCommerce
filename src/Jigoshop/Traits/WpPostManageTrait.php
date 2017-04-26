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
        $postContent = method_exists($object, 'getDescription') ? $object->getDescription() :
            (method_exists($object, 'getText') ? $object->getText() : '');

        $wpdb->insert($wpdb->posts, [
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
            'post_content' => $postContent,
        ]);

        return $wpdb->insert_id;
    }

    /**
     * updates post with specified type and id, returns its id
     * @param $wp (WP instance)
     * @param $object
     * @param $postType
     * @return int
     */
    public function updatePost($wp, $object, $postType)
    {
        $wpdb = $wp->getWPDB();
        $date = $wp->getHelpers()->currentTime('mysql');
        $dateGmt = $wp->getHelpers()->currentTime('mysql', true);
        //assign title from one of post methods to get title or name
        $title = method_exists($object, 'getTitle') ? $object->getTitle() :
            (method_exists($object, 'getName') ? $object->getName() : null);
        $postContent = method_exists($object, 'getDescription') ? $object->getDescription() :
            (method_exists($object, 'getText') ? $object->getText() : '');

        $wpdb->update($wpdb->posts, [
            'post_modified' => $date,
            'post_modified_gmt' => $dateGmt,
            'post_type' => $postType,
            'post_title' => $title,
            'post_name' => sanitize_title($title),
            'post_content' => $postContent,
        ],
            ['id' => $object->getId()]
        );

        return $object->getId();
    }
}