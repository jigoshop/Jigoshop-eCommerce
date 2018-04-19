<?php

namespace Jigoshop\Core\Types;

use WPAL\Wordpress;

class Coupon implements Post
{
	const NAME = 'shop_coupon';

	/** @var \WPAL\Wordpress */
	private $wp;

	public function __construct(Wordpress $wp)
	{
		$this->wp = $wp;
		$wp->doAction('jigoshop\coupon\type\init', $wp);
	}

	public function getName()
	{
		return self::NAME;
	}

	public function getDefinition()
	{
		return [
			'labels' => [
				'menu_name' => __('Coupons', 'jigoshop-ecommerce'),
				'name' => __('Coupons', 'jigoshop-ecommerce'),
				'singular_name' => __('Coupon', 'jigoshop-ecommerce'),
				'add_new' => __('Add Coupon', 'jigoshop-ecommerce'),
				'add_new_item' => __('Add New Coupon', 'jigoshop-ecommerce'),
				'edit' => __('Edit', 'jigoshop-ecommerce'),
				'edit_item' => __('Edit Coupon', 'jigoshop-ecommerce'),
				'new_item' => __('New Coupon', 'jigoshop-ecommerce'),
				'view' => __('View Coupons', 'jigoshop-ecommerce'),
				'view_item' => __('View Coupon', 'jigoshop-ecommerce'),
				'search_items' => __('Search Coupons', 'jigoshop-ecommerce'),
				'not_found' => __('No Coupons found', 'jigoshop-ecommerce'),
				'not_found_in_trash' => __('No Coupons found in trash', 'jigoshop-ecommerce'),
				'parent' => __('Parent Coupon', 'jigoshop-ecommerce')
            ],
			'description' => __('This is where you can add new coupons that customers can use in your store.', 'jigoshop-ecommerce'),
			'public' => true,
			'show_ui' => true,
			'capability_type' => self::NAME,
			'map_meta_cap' => true,
			'publicly_queryable' => false,
			'exclude_from_search' => true,
			'hierarchical' => false,
			'rewrite' => false,
			'query_var' => true,
			'supports' => ['title'],
			'show_in_nav_menus' => false,
			'show_in_menu' => 'jigoshop'
        ];
	}
}
