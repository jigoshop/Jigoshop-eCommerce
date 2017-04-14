<?php

namespace Jigoshop\Widget;

use Jigoshop\Helper\Render;

class ProductSearch extends \WP_Widget
{
	const ID = 'jigoshop_product_search';

	public function __construct()
	{
		$options = [
			'classname' => self::ID,
			'description' => __('A search form for your products', 'jigoshop')
        ];

		// Create the widget
		parent::__construct(self::ID, __('Jigoshop: Product Search', 'jigoshop'), $options);

		// Add own hidden fields to filter
		add_filter('jigoshop\get_fields', [$this, 'hiddenFields']);
	}

	public function hiddenFields($fields)
	{
		if (isset($_GET['s'])) {
			$fields['s'] = $_GET['s'];
		}

		return $fields;
	}

	/**
	 * Display the widget in the sidebar.
	 *
	 * @param array $args     Sidebar arguments.
	 * @param array $instance The instance.
	 *
	 * @return bool|void
	 */
	public function widget($args, $instance)
	{
		// Extract the widget arguments
		extract($args);

		// Set the widget title
		$title = apply_filters(
			'widget_title',
			($instance['title']) ? $instance['title'] : __('Product Search', 'jigoshop'),
			$instance,
			$this->id_base
		);

		$fields = apply_filters('jigoshop\get_fields', []);
		Render::output('widget/product_search/widget', array_merge($args, [
			'title' => $title,
			'fields' => $fields,
        ]));
	}

	/**
	 * Handles the processing of information entered in the wordpress admin
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
		$instance['title'] = strip_tags($new_instance['title']);

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
		// Get instance data
		$title = isset($instance['title']) ? esc_attr($instance['title']) : null;

		Render::output('widget/product_search/form', [
			'title_id' => $this->get_field_id('title'),
			'title_name' => $this->get_field_name('title'),
			'title' => $title,
        ]);
	}
}
