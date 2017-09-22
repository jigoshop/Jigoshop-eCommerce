<?php

namespace Jigoshop\Core\Types;

use Jigoshop\Core\Types;
use Jigoshop\Entity\Order\Status;
use WPAL\Wordpress;

class Order implements Post
{
	const NAME = 'shop_order';

	/** @var \WPAL\Wordpress */
	private $wp;

	public function __construct(Wordpress $wp)
	{
		$this->wp = $wp;

		$wp->addAction('init', [$this, 'registerOrderStatuses']);
		$wp->addFilter('post_updated_messages', [$this, 'updateMessages']);
		// Enable comments for all orders, disable pings
		$wp->addFilter('wp_insert_post_data', function ($data){
			if ($data['post_type'] == Order::NAME) {
				$data['comment_status'] = 'open';
				$data['ping_status'] = 'closed';
			}

			return $data;
		});
	}

	public function getName()
	{
		return self::NAME;
	}

	public function getDefinition()
	{
		return [
			'labels' => [
				'name' => __('Orders', 'jigoshop-ecommerce'),
				'singular_name' => __('Order', 'jigoshop-ecommerce'),
				'all_items' => __('All orders', 'jigoshop-ecommerce'),
				'add_new' => __('Add new', 'jigoshop-ecommerce'),
				'add_new_item' => __('New order', 'jigoshop-ecommerce'),
				'edit' => __('Edit', 'jigoshop-ecommerce'),
				'edit_item' => __('Edit order', 'jigoshop-ecommerce'),
				'new_item' => __('New order', 'jigoshop-ecommerce'),
				'view' => __('View order', 'jigoshop-ecommerce'),
				'view_item' => __('View order', 'jigoshop-ecommerce'),
				'search_items' => __('Search', 'jigoshop-ecommerce'),
				'not_found' => __('No orders found', 'jigoshop-ecommerce'),
				'not_found_in_trash' => __('No orders found in trash', 'jigoshop-ecommerce'),
				'parent' => __('Parent orders', 'jigoshop-ecommerce')
            ],
			'description' => __('This is where store orders are stored.', 'jigoshop-ecommerce'),
			'public' => false,
			'show_ui' => true,
			'show_in_nav_menus' => false,
			'publicly_queryable' => false,
			'exclude_from_search' => true,
			'capability_type' => self::NAME,
			'map_meta_cap' => true,
			'hierarchical' => false,
			'rewrite' => false,
			'query_var' => false,
			'supports' => ['title', 'comments'],
			'has_archive' => false,
			'menu_position' => 58,
			'menu_icon' => 'dashicons-clipboard',
        ];
	}

	public function registerOrderStatuses()
	{
		$statuses = Status::getStatuses();
		foreach ($statuses as $status => $label) {
			$this->wp->registerPostStatus($status, [
				'label' => $label,
				'public' => false,
				'exclude_from_search' => false,
				'show_in_admin_all_list' => true,
				'show_in_admin_status_list' => true,
				'label_count' => _n_noop($label.' <span class="count">(%s)</span>', $label.' <span class="count">(%s)</span>', 'jigoshop'),
            ]);
		}
	}

	public function updateMessages($messages)
	{
		if ($this->wp->getPostType() === self::NAME) {
			$messages['post'][1] = __('Order updated.', 'jigoshop-ecommerce');
			$messages['post'][4] = __('Order updated.', 'jigoshop-ecommerce');
			$messages['post'][6] = __('Order updated.', 'jigoshop-ecommerce');

			$messages['post'][8] = __('Order submitted.', 'jigoshop-ecommerce');
			$messages['post'][10] = __('Order draft updated.', 'jigoshop-ecommerce');
		}

		return $messages;
	}
}
