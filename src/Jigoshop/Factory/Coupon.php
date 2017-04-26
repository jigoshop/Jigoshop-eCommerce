<?php

namespace Jigoshop\Factory;

use Jigoshop\Core\Types;
use Jigoshop\Entity\Coupon as Entity;
use WPAL\Wordpress;

/**
 * Coupon factory.
 *
 * @package Jigoshop\Factory
 */
class Coupon implements EntityFactoryInterface
{
    /** @var Wordpress */
    private $wp;

    public function __construct(Wordpress $wp)
    {
        $this->wp = $wp;
    }

    /**
     * Creates new coupon properly based on POST variable data.
     *
     * @param $id int Post ID to create object for.
     *
     * @return \Jigoshop\Entity\Coupon
     */
    public function create($id)
    {
        $coupon = new Entity();
        $coupon->setId($id);

        if (!empty($_POST)) {

            $helpers = $this->wp->getHelpers();
            $coupon->setTitle($helpers->sanitizeTitle($_POST['post_title']));

            $this->convertData($_POST);
            $coupon->restoreState($_POST['jigoshop_coupon']);
        }

        return $coupon;
    }

    /**
     * Updates coupon properties based on array data.
     *
     * @param $coupon \Jigoshop\Entity\Coupon for update.
     * @param $data array of data for update.
     *
     * @return \Jigoshop\Entity\Coupon
     */
    public function update(Entity $coupon, $data)
    {
        if (!empty($data)) {
            $helpers = $this->wp->getHelpers();
            $coupon->setTitle($helpers->sanitizeTitle($data['post_title']));
            $this->convertData($data);
            $coupon->restoreState($data['jigoshop_coupon']);
        }

        return $coupon;
    }

    /**
     * Fetches product from database.
     *
     * @param $post \WP_Post Post to fetch coupon for.
     *
     * @return \Jigoshop\Entity\Coupon
     */
    public function fetch($post)
    {
        if($post && ($post->post_type != Types::COUPON)) {
            return null;
        }

        $state = [];
        $coupon = null;
        if ($post) {
            $coupon = new Entity();
            $state = array_map(function ($item) {
                return $item[0];
            }, $this->wp->getPostMeta($post->ID));

            $coupon->setId($post->ID);
            $coupon->setTitle($post->post_title);
            $coupon->setCode($post->post_name);

            if (isset($state['products'])) {
                $state['products'] = unserialize($state['products']);
            }
            if (isset($state['excluded_products'])) {
                $state['excluded_products'] = unserialize($state['excluded_products']);
            }
            if (isset($state['categories'])) {
                $state['categories'] = unserialize($state['categories']);
            }
            if (isset($state['excluded_categories'])) {
                $state['excluded_categories'] = unserialize($state['excluded_categories']);
            }
            if (isset($state['payment_methods'])) {
                $state['payment_methods'] = unserialize($state['payment_methods']);
            }

            $coupon->restoreState($state);
        }

        return $this->wp->applyFilters('jigoshop\find\coupon', $coupon, $state);
    }

    /**
     * converting input data to be readable by db
     * @param array $data
     * @return array
     */
    private function convertData(array &$data)
    {
        $data['jigoshop_coupon']['individual_use'] = $data['jigoshop_coupon']['individual_use'] == 'on';
        $data['jigoshop_coupon']['free_shipping'] = $data['jigoshop_coupon']['free_shipping'] == 'on';
        $data['jigoshop_coupon']['products'] = array_filter(explode(',', $data['jigoshop_coupon']['products']));
        $data['jigoshop_coupon']['excluded_products'] = array_filter(explode(',',
            $data['jigoshop_coupon']['excluded_products']));
        $data['jigoshop_coupon']['categories'] = array_filter(explode(',', $data['jigoshop_coupon']['categories']));
        $data['jigoshop_coupon']['excluded_categories'] = array_filter(explode(',',
            $data['jigoshop_coupon']['excluded_categories']));
        if (!empty($data['jigoshop_coupon']['from'])) {
            $data['jigoshop_coupon']['from'] = strtotime($data['jigoshop_coupon']['from']);
        } else {
            $data['jigoshop_coupon']['from'] = false;
        }
        if (!empty($data['jigoshop_coupon']['to'])) {
            $data['jigoshop_coupon']['to'] = strtotime($data['jigoshop_coupon']['to']);
        } else {
            $data['jigoshop_coupon']['to'] = false;
        }
    }
}
