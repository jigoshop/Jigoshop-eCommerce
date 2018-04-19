<?php

namespace Jigoshop\Widget;

use Jigoshop\Core;
use Jigoshop\Helper\Render;

class ProductTagCloud extends \WP_Widget
{
	const ID = 'jigoshop_product_tag_cloud';

	public function __construct()
	{
		$options = [
			'classname' => self::ID,
			'description' => __('Your most used product tags in cloud format', 'jigoshop-ecommerce'),
        ];

		// Create the widget
		parent::__construct(self::ID, __('Jigoshop: Product Tag Cloud', 'jigoshop-ecommerce'), $options);

		// Flush cache after every save
		add_action('save_post', [$this, 'deleteTransient']);
		add_action('deleted_post', [$this, 'deleteTransient']);
		add_action('switch_theme', [$this, 'deleteTransient']);
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
		// Get the widget cache from the transient
		$cache = get_transient(Core::WIDGET_CACHE);

		// If this tag cloud widget instance is cached, get from the cache
		if (isset($cache[$args['widget_id']])) {
			echo $cache[$args['widget_id']];

			return;
		}

		// Otherwise Start buffering and output the Widget
		ob_start();

		// Set the widget title
		$title = apply_filters(
			'widget_title',
			($instance['title']) ? $instance['title'] : __('Product Tags', 'jigoshop-ecommerce'),
			$instance,
			$this->id_base
		);

		Render::output('widget/product_tag_cloud/widget', array_merge($args, [
			'title' => $title,
        ]));

		// Flush output buffer and save to transient cache
		$cache[$args['widget_id']] = ob_get_flush();
		set_transient(Core::WIDGET_CACHE, $cache, 3600 * 3); // 3 hours ahead
	}

	public function deleteTransient()
	{
		delete_transient(Core::WIDGET_CACHE);
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

		// Save new values
		$instance['title'] = strip_tags(stripslashes($new_instance['title']));

		// Flush the cache
		$this->deleteTransient();

		return $instance;
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
		$title = (isset($instance['title'])) ? esc_attr($instance['title']) : null;

		Render::output('widget/product_tag_cloud/form', [
			'title_id' => $this->get_field_id('title'),
			'title_name' => $this->get_field_name('title'),
			'title' => $title,
        ]);
	}
}
