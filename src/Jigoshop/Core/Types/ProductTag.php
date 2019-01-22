<?php

namespace Jigoshop\Core\Types;

use Jigoshop\Core\Options;

class ProductTag implements Taxonomy
{
	const NAME = 'product_tag';

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
				'menu_name' => __('Tags', 'jigoshop-ecommerce'),
				'name' => __('Product Tags', 'jigoshop-ecommerce'),
				'singular_name' => __('Product Tag', 'jigoshop-ecommerce'),
				'search_items' => __('Search Product Tags', 'jigoshop-ecommerce'),
				'all_items' => __('All Product Tags', 'jigoshop-ecommerce'),
				'parent_item' => __('Parent Product Tag', 'jigoshop-ecommerce'),
				'parent_item_colon' => __('Parent Product Tag:', 'jigoshop-ecommerce'),
				'edit_item' => __('Edit Product Tag', 'jigoshop-ecommerce'),
				'update_item' => __('Update Product Tag', 'jigoshop-ecommerce'),
				'add_new_item' => __('Add New Product Tag', 'jigoshop-ecommerce'),
				'new_item_name' => __('New Product Tag Name', 'jigoshop-ecommerce')
            ],
			'capabilities' => [
				'manage_terms' => 'manage_product_terms',
				'edit_terms' => 'edit_product_terms',
				'delete_terms' => 'delete_product_terms',
				'assign_terms' => 'assign_product_terms',
            ],
            'show_in_rest' => true,
			'hierarchical' => false,
			'show_ui' => true,
			'query_var' => true,
			'rewrite' => [
				'slug' => $this->options->get('permalinks.tag'),
				'with_front' => $this->options->get('permalinks.with_front'),
				'feeds' => false,
				'pages' => true,
				'ep_mask' => EP_ALL,
            ],
        ];
	}
}
