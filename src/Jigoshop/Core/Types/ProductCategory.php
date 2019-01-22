<?php

namespace Jigoshop\Core\Types;

use Jigoshop\Core\Options;

class ProductCategory implements Taxonomy
{
	const NAME = 'product_category';

	/** @var Options */
	private $options;

	public function __construct(Options $options)
	{
		$this->options = $options;
	}

	/**
	 * Returns name which taxonomy will be registered under.
	 *
	 * @return string
	 */
	public function getName()
	{
		return self::NAME;
	}

	/**
	 * Returns list of parent post types which taxonomy will be registered under.
	 *
	 * @return array
	 */
	public function getPostTypes()
	{
		return [Product::NAME];
	}

	/**
	 * Returns full definition of the taxonomy.
	 *
	 * @return array
	 */
	public function getDefinition()
	{
		return [
			'labels' => [
				'menu_name' => __('Categories', 'jigoshop-ecommerce'),
				'name' => __('Product Categories', 'jigoshop-ecommerce'),
				'singular_name' => __('Product Category', 'jigoshop-ecommerce'),
				'search_items' => __('Search Product Categories', 'jigoshop-ecommerce'),
				'all_items' => __('All Product Categories', 'jigoshop-ecommerce'),
				'parent_item' => __('Parent Product Category', 'jigoshop-ecommerce'),
				'parent_item_colon' => __('Parent Product Category:', 'jigoshop-ecommerce'),
				'edit_item' => __('Edit Product Category', 'jigoshop-ecommerce'),
				'update_item' => __('Update Product Category', 'jigoshop-ecommerce'),
				'add_new_item' => __('Add New Product Category', 'jigoshop-ecommerce'),
				'new_item_name' => __('New Product Category Name', 'jigoshop-ecommerce'),
            ],
			'capabilities' => [
				'manage_terms' => 'manage_product_terms',
				'edit_terms' => 'edit_product_terms',
				'delete_terms' => 'delete_product_terms',
				'assign_terms' => 'assign_product_terms',
            ],
            'show_in_rest' => true,
			'hierarchical' => true,
			'show_ui' => true,
			'show_in_menu' => false,
			'query_var' => true,
            'show_in_nav_menus' => true,
			'rewrite' => [
				'slug' => $this->options->get('permalinks.category'),
				'with_front' => $this->options->get('permalinks.with_front'),
				'feeds' => false,
				'pages' => true,
				'ep_mask' => EP_ALL,
            ],
        ];
	}
}
