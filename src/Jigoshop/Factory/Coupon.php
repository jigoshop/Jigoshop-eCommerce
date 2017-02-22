<?php

namespace Jigoshop\Factory;

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
	 * Creates new product properly based on POST variable data.
	 *
	 * @param $id int Post ID to create object for.
	 *
	 * @return \Jigoshop\Entity\Product
	 */
	public function create($id)
	{
		$coupon = new Entity();
		$coupon->setId($id);

		if (!empty($_POST)) {
			$helpers = $this->wp->getHelpers();
			$coupon->setTitle($helpers->sanitizeTitle($_POST['post_title']));

			$_POST['jigoshop_coupon']['individual_use'] = $_POST['jigoshop_coupon']['individual_use'] == 'on';
			$_POST['jigoshop_coupon']['free_shipping'] = $_POST['jigoshop_coupon']['free_shipping'] == 'on';
			$_POST['jigoshop_coupon']['products'] = array_filter(explode(',', $_POST['jigoshop_coupon']['products']));
			$_POST['jigoshop_coupon']['excluded_products'] = array_filter(explode(',', $_POST['jigoshop_coupon']['excluded_products']));
			$_POST['jigoshop_coupon']['categories'] = array_filter(explode(',', $_POST['jigoshop_coupon']['categories']));
			$_POST['jigoshop_coupon']['excluded_categories'] = array_filter(explode(',', $_POST['jigoshop_coupon']['excluded_categories']));
			if(!empty($_POST['jigoshop_coupon']['from'])) {
				$_POST['jigoshop_coupon']['from'] = strtotime($_POST['jigoshop_coupon']['from']);
			} else {
				$_POST['jigoshop_coupon']['from'] = false;
			}
			if(!empty($_POST['jigoshop_coupon']['to'])) {
				$_POST['jigoshop_coupon']['to'] = strtotime($_POST['jigoshop_coupon']['to']);
			} else {
				$_POST['jigoshop_coupon']['to'] = false;
			}

			$coupon->restoreState($_POST['jigoshop_coupon']);
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
		$state = array();
        $coupon = null;
		if ($post) {
            $coupon = new Entity();
			$state = array_map(function ($item){
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
}
