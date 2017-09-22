<?php

namespace Jigoshop\Core\Types;

use WPAL\Wordpress;

class Email implements Post
{
	const NAME = 'shop_email';

	/** @var \WPAL\Wordpress */
	private $wp;

	public function __construct(Wordpress $wp)
	{
		$this->wp = $wp;
		$wp->doAction('jigoshop\email\type\init', $wp);
	}

	public function getName()
	{
		return self::NAME;
	}

	public function getDefinition()
	{
		return [
			'labels' => [
				'menu_name' => __('Emails', 'jigoshop-ecommerce'),
				'name' => __('Emails', 'jigoshop-ecommerce'),
				'singular_name' => __('Emails', 'jigoshop-ecommerce'),
				'add_new' => __('Add Email', 'jigoshop-ecommerce'),
				'add_new_item' => __('Add New Email', 'jigoshop-ecommerce'),
				'edit' => __('Edit', 'jigoshop-ecommerce'),
				'edit_item' => __('Edit Email', 'jigoshop-ecommerce'),
				'new_item' => __('New Email', 'jigoshop-ecommerce'),
				'view' => __('View Email', 'jigoshop-ecommerce'),
				'view_item' => __('View Email', 'jigoshop-ecommerce'),
				'search_items' => __('Search Email', 'jigoshop-ecommerce'),
				'not_found' => __('No Emils found', 'jigoshop-ecommerce'),
				'not_found_in_trash' => __('No Emails found in trash', 'jigoshop-ecommerce'),
				'parent' => __('Parent Email', 'jigoshop-ecommerce')
            ],
			'description' => __('This is where you can add new emails that customers can receive in your store.', 'jigoshop-ecommerce'),
			'public' => true,
			'show_ui' => true,
			'capability_type' => self::NAME,
			'map_meta_cap' => true,
			'publicly_queryable' => false,
			'exclude_from_search' => true,
			'hierarchical' => false,
			'rewrite' => false,
			'query_var' => true,
			'supports' => ['title', 'editor'],
			'show_in_nav_menus' => false,
			'show_in_menu' => 'jigoshop'
        ];
	}
}
