<?php

namespace Jigoshop\Core\Types;

use Jigoshop\Container;
use Jigoshop\Core\Options;
use Jigoshop\Core\Types;
use Jigoshop\Entity\Product\Variable;
use Jigoshop\Exception;
use Jigoshop\Service\ProductServiceInterface;
use Monolog\Registry;
use WPAL\Wordpress;

class Product implements Post
{
	const NAME = 'product';

	/** @var \WPAL\Wordpress */
	private $wp;
	/** @var Options */
	private $options;
	/** @var array */
	private $enabledTypes = [];

	public function __construct(Container $di, Wordpress $wp, Options $options, ProductServiceInterface $productService)
	{
		$this->wp = $wp;
		$this->options = $options;

		$types = $options->getEnabledProductTypes();
		foreach ($types as $typeClass) {
			/** @var Types\Product\Type $type */
			$type = $di->get($typeClass);

			if (!($type instanceof Types\Product\Type)) {
				if (WP_DEBUG) {
					throw new Exception(sprintf(__('Invalid type definition! Offending class: "%s".', 'jigoshop-ecommerce'), $typeClass));
				}

				Registry::getInstance(JIGOSHOP_LOGGER)->addWarning(sprintf('Invalid type definition! Offending class: "%s".', $typeClass));
				continue;
			}

			$this->enabledTypes[$type->getId()] = $type;
			$productService->addType($type->getId(), $type->getClass());
			$wp->addAction('jigoshop\product\type\init', [$type, 'initialize'], 10, 2);
		}

		$wp->doAction('jigoshop\product\type\init', $wp, $this->enabledTypes);
		// Enable comments for all orders, disable pings
		$wp->addFilter('wp_insert_post_data', function ($data){
			if ($data['post_type'] == Product::NAME) {
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
				'name' => __('Products', 'jigoshop-ecommerce'),
				'singular_name' => __('Product', 'jigoshop-ecommerce'),
				'all_items' => __('All Products', 'jigoshop-ecommerce'),
				'add_new' => __('Add New', 'jigoshop-ecommerce'),
				'add_new_item' => __('Add New Product', 'jigoshop-ecommerce'),
				'edit' => __('Edit', 'jigoshop-ecommerce'),
				'edit_item' => __('Edit Product', 'jigoshop-ecommerce'),
				'new_item' => __('New Product', 'jigoshop-ecommerce'),
				'view' => __('View Product', 'jigoshop-ecommerce'),
				'view_item' => __('View Product', 'jigoshop-ecommerce'),
				'search_items' => __('Search Products', 'jigoshop-ecommerce'),
				'not_found' => __('No Products found', 'jigoshop-ecommerce'),
				'not_found_in_trash' => __('No Products found in trash', 'jigoshop-ecommerce'),
				'parent' => __('Parent Product', 'jigoshop-ecommerce'),
            ],
			'description' => __('This is where you can add new products to your store.', 'jigoshop-ecommerce'),
			'public' => true,
			'show_ui' => true,
            'show_in_rest' => true,
            'rest_base' => self::NAME,
            'rest_controller_class' => 'WP_REST_Posts_Controller',
			'capability_type' => self::NAME,
			'map_meta_cap' => true,
			'publicly_queryable' => true,
			'exclude_from_search' => false,
			'hierarchical' => false, // Hierarchical causes a memory leak http://core.trac.wordpress.org/ticket/15459
			'rewrite' => [
				'slug' => $this->options->get('permalinks.product'),
				'with_front' => $this->options->get('permalinks.with_front'),
				'feeds' => true,
				'pages' => true,
            ],
			'query_var' => true,
			'supports' => ['title', 'editor', 'thumbnail', 'comments', 'excerpt', 'revisions'],
			'has_archive' => true,
			'show_in_nav_menus' => false,
			'menu_position' => 56,
			'menu_icon' => 'dashicons-book',
        ];
	}

	/**
	 * @return array
	 */
	public function getEnabledTypes()
	{
		return $this->enabledTypes;
	}

	/**
	 * Finds and returns type instance of specified product type.
	 *
	 * @param $type string Name of the type.
	 *
	 * @return Types\Product\Type Type instance.
	 */
	public function getType($type)
	{
		if (!isset($this->enabledTypes[$type])) {
			throw new Exception(sprintf(__('Unknown type: "%s".', 'jigoshop-ecommerce'), $type));
		}

		return $this->enabledTypes[$type];
	}
}
