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
				'menu_name' => __('Tags', 'jigoshop'),
				'name' => __('Product Tags', 'jigoshop'),
				'singular_name' => __('Product Tag', 'jigoshop'),
				'search_items' => __('Search Product Tags', 'jigoshop'),
				'all_items' => __('All Product Tags', 'jigoshop'),
				'parent_item' => __('Parent Product Tag', 'jigoshop'),
				'parent_item_colon' => __('Parent Product Tag:', 'jigoshop'),
				'edit_item' => __('Edit Product Tag', 'jigoshop'),
				'update_item' => __('Update Product Tag', 'jigoshop'),
				'add_new_item' => __('Add New Product Tag', 'jigoshop'),
				'new_item_name' => __('New Product Tag Name', 'jigoshop')
            ],
			'capabilities' => [
				'manage_terms' => 'manage_product_terms',
				'edit_terms' => 'edit_product_terms',
				'delete_terms' => 'delete_product_terms',
				'assign_terms' => 'assign_product_terms',
            ],
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
