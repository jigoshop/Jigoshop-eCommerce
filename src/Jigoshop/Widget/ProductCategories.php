<?php

namespace Jigoshop\Widget;

use Jigoshop\Core;
use Jigoshop\Frontend\Pages;
use Jigoshop\Helper\Render;
use Jigoshop\Service\ProductServiceInterface;
use Jigoshop\Web\CategoryWalker;
use WPAL\Wordpress;

class ProductCategories extends \WP_Widget
{
	const ID = 'jigoshop_product_categories';

	/** @var ProductServiceInterface */
	private static $productService;
	/** @var Wordpress */
	private static $wp;
	/** @var Core\Options */
	private static $options;

	public function __construct()
	{
		$options = [
			'classname' => self::ID,
			'description' => __('A list or dropdown of product categories', 'jigoshop-ecommerce'),
        ];

		// Create the widget
		parent::__construct(self::ID, __('Jigoshop: Product Categories', 'jigoshop-ecommerce'), $options);

		// Flush cache after every save
		add_action('save_post', [$this, 'deleteTransient']);
		add_action('deleted_post', [$this, 'deleteTransient']);
		add_action('switch_theme', [$this, 'deleteTransient']);
	}

	public static function setProductService($productService)
	{
		self::$productService = $productService;
	}

	public static function setWp($wp)
	{
		self::$wp = $wp;
	}

	public static function setOptions($options)
	{
		self::$options = $options;
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
        $oneLevelOnly = isset($instance['one_level_only']) ? $instance['one_level_only'] : false;

		// If cached get from the cache
		if (isset($cache[$args['widget_id']]) && !$oneLevelOnly) {
			echo $cache[$args['widget_id']];

			return;
		}

		// Otherwise Start buffering and output the Widget
		ob_start();

		// Set the widget title
		$title = apply_filters(
			'widget_title',
			($instance['title']) ? $instance['title'] : __('Product Categories', 'jigoshop-ecommerce'),
			$instance,
			$this->id_base
		);

		// Get options
		$count = isset($instance['count']) ? $instance['count'] : false;
		$is_hierarchical = isset($instance['hierarchical']) ? $instance['hierarchical'] : false;
		$is_dropdown = isset($instance['dropdown']) ? $instance['dropdown'] : false;

		$query = [
			'orderby' => 'name',
			'show_count' => $count,
			'hierarchical' => $is_hierarchical,
			'taxonomy' => Core\Types::PRODUCT_CATEGORY,
			'title_li' => null,
            'hide_title_if_empty' => true
        ];

		if($oneLevelOnly) {
		    /** @var \WP_Term $queriedTerm */
            $queriedTerm = self::$wp->getTermBy(
                'slug',
                self::$wp->getQueryParameter(Core\Types::PRODUCT_CATEGORY),
                Core\Types::PRODUCT_CATEGORY
            );
            if($queriedTerm) {
                $query['parent'] = $queriedTerm->term_id;
            } else {
                $query['parent'] = 0;
            }
        }

		if (Pages::isProduct()) {
			global $post;
			$categories = get_the_terms($post->ID, Core\Types::PRODUCT_CATEGORY);
			if (!empty($categories)) {
				$category = reset($categories);
				$query['current_category'] = apply_filters('jigoshop_product_cat_widget_terms', $category->term_id, $categories);
			}
		}

		if ($is_dropdown) {
			global $wp_query;

			$query = [
				'pad_counts' => 1,
				'hierarchical' => $is_hierarchical,
				'hide_empty' => true,
				'show_count' => $count,
				'selected' => isset($wp_query->query[Core\Types::PRODUCT_CATEGORY]) ? $wp_query->query[Core\Types::PRODUCT_CATEGORY] : '',
            ];

			$terms = get_terms(Core\Types::PRODUCT_CATEGORY, $query);
			if (!$terms) {
				return;
			}

			$walker = new CategoryWalker(self::$wp, 'widget/product_categories/item');

			Render::output('widget/product_categories/dropdown', array_merge($args, [
				'title' => $title,
				'query' => $query,
				'walker' => $walker,
				'terms' => $terms,
				'value' => $query['selected'],
				'shopUrl' => get_permalink(self::$options->getPageId(Pages::SHOP)),
            ]));
		} else {
            $terms = get_terms(Core\Types::PRODUCT_CATEGORY, $query);
            if (!$terms) {
                return;
            }
			Render::output('widget/product_categories/list', array_merge($args, [
				'title' => $title,
				'args' => $query,
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
	function update($new_instance, $old_instance)
	{
		$instance = $old_instance;

		// Save the new values
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['dropdown'] = isset($new_instance['dropdown']) ? $new_instance['dropdown'] == 'on' : false;
		$instance['count'] = isset($new_instance['count']) ? $new_instance['count'] == 'on' : false;
		$instance['hierarchical'] = isset($new_instance['hierarchical']) ? $new_instance['hierarchical'] == 'on' : false;
		$instance['one_level_only'] = isset($new_instance['one_level_only']) ? $new_instance['one_level_only'] == 'on' : false;

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
	function form($instance)
	{
		// Get values from instance
		$title = isset($instance['title']) ? esc_attr($instance['title']) : null;
		$dropdown = isset($instance['dropdown']) ? $instance['dropdown'] : false;
		$count = isset($instance['count']) ? $instance['count'] : false;
		$hierarchical = isset($instance['hierarchical']) ? $instance['hierarchical'] : false;
		$oneLevelOnly = isset($instance['one_level_only']) ? $instance['one_level_only'] : false;


		Render::output('widget/product_categories/form', [
			'title_id' => $this->get_field_id('title'),
			'title_name' => $this->get_field_name('title'),
			'title' => $title,
			'dropdown_id' => $this->get_field_id('dropdown'),
			'dropdown_name' => $this->get_field_name('dropdown'),
			'dropdown' => $dropdown,
			'count_id' => $this->get_field_id('count'),
			'count_name' => $this->get_field_name('count'),
			'count' => $count,
			'hierarchical_id' => $this->get_field_id('hierarchical'),
			'hierarchical_name' => $this->get_field_name('hierarchical'),
			'hierarchical' => $hierarchical,
            'one_level_only_id' => $this->get_field_id('one_level_only'),
			'one_level_only_name' => $this->get_field_name('one_level_only'),
			'one_level_only' => $oneLevelOnly,
        ]);
	}
}
