<?php

namespace Jigoshop\Widget;

use Jigoshop\Core;
use Jigoshop\Core\Types;
use Jigoshop\Entity\Product;
use Jigoshop\Helper\Render;
use Jigoshop\Service\ProductServiceInterface;
use WPAL\Wordpress;

class BestSellers extends \WP_Widget
{
	const ID = 'jigoshop_best_sellers';

	/** @var ProductServiceInterface */
	private static $productService;

	public function __construct()
	{
		$options = [
			'classname' => self::ID,
			'description' => __('Lists the best selling products', 'jigoshop-ecommerce')
        ];

		// Create the widget
		parent::__construct(self::ID, __('Jigoshop: Best Sellers', 'jigoshop-ecommerce'), $options);

		// Flush cache after every save
		add_action('save_post', [$this, 'deleteTransient']);
		add_action('deleted_post', [$this, 'deleteTransient']);
		add_action('switch_theme', [$this, 'deleteTransient']);
	}

	public static function setProductService($productService)
	{
		self::$productService = $productService;
	}

	/**
	 * Displays the widget in the sidebar.
	 *
	 * @param array $args     Sidebar arguments.
	 * @param array $instance The instance.
	 *
	 * @return bool|void
	 */
	public function widget($args, $instance)
	{
		// Get the best selling products from the transient
		$cache = get_transient(Core::WIDGET_CACHE);

		// If cached get from the cache
		if (isset($cache[$args['widget_id']])) {
			echo $cache[$args['widget_id']];

			return;
		}

		// Start buffering
		ob_start();

		// Set the widget title
		$title = apply_filters(
			'widget_title',
			($instance['title']) ? $instance['title'] : __('Best Sellers', 'jigoshop-ecommerce'),
			$instance,
			$this->id_base
		);

		// Set number of products to fetch
		if (!$number = absint($instance['number'])) {
			$number = 5;
		}

		// Set up query
		$query_args = [
			'posts_per_page' => $number,
			'post_type' => Types::PRODUCT,
			'post_status' => 'publish',
			'meta_key' => 'stock_sold',
			'orderby' => 'meta_value_num+0',
			'order' => 'desc',
			'nopaging' => false,
			'meta_query' => [
				[
					'key' => 'visibility',
					'value' => [Product::VISIBILITY_CATALOG, Product::VISIBILITY_PUBLIC],
					'compare' => 'IN',
                ],
            ]
        ];

		// Run the query
		$q = new \WP_Query($query_args);
		$products = self::$productService->findByQuery($q);

		if (!empty($products)) {
			Render::output('widget/best_sellers/widget', array_merge($args, [
				'title' => $title,
				'products' => $products,
                'style' => isset($instance['style']) ? $instance['style'] : 'compact',
            ]));
		}

		// Flush output buffer and save to transient cache
		$cache[$args['widget_id']] = ob_get_flush();
		set_transient(Core::WIDGET_CACHE, $cache, 3600 * 3); // 3 hours ahead
	}

	/**
	 * Handles the processing of information entered in the wordpress admin
	 * Flushes the cache & removes entry from options array
	 *
	 * @param array $new_instance new instance
	 * @param array $old_instance old instance
	 *
	 * @return array instance
	 */
	public function update($new_instance, $old_instance)
	{
		$instance = $old_instance;

		// Save the new values
		$instance['title'] = trim(strip_tags($new_instance['title']));
		$instance['number'] = absint($new_instance['number']);
		$instance['style'] = $new_instance['style'];

		// Flush the cache
		$this->deleteTransient();

		return $instance;
	}

	public function deleteTransient()
	{
		delete_transient(Core::WIDGET_CACHE);
	}

	/**
	 * Displays the form for the wordpress admin.
	 *
	 * @param array $instance Instance data.
	 *
	 * @return string|void
	 */
	public function form($instance)
	{
		// Get instance data
		$title = isset($instance['title']) ? esc_attr($instance['title']) : null;
		$number = isset($instance['number']) ? absint($instance['number']) : 5;
		$style = isset($instance['style']) ? $instance['style'] : 'compact';

		Render::output('widget/best_sellers/form', [
			'title_id' => $this->get_field_id('title'),
			'title_name' => $this->get_field_name('title'),
			'title' => $title,
			'number_id' => $this->get_field_id('number'),
			'number_name' => $this->get_field_name('number'),
			'number' => $number,
            'style_id' => $this->get_field_id('style'),
            'style_name' => $this->get_field_name('style'),
            'style' => $style
        ]);
	}
}
