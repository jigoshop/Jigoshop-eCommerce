<?php

namespace Jigoshop\Admin\Page;

use Jigoshop\Core\Options;
use Jigoshop\Core\Types;
use Jigoshop\Entity\Product as ProductEntity;
use Jigoshop\Entity\Product;
use Jigoshop\Helper\Formatter;
use Jigoshop\Helper\Product as ProductHelper;
use Jigoshop\Helper\Render;
use Jigoshop\Helper\Scripts;
use Jigoshop\Helper\Styles;
use Jigoshop\Service\ProductServiceInterface;
use WPAL\Wordpress;

class Products
{
	/** @var \WPAL\Wordpress */
	private $wp;
	/** @var \Jigoshop\Core\Options */
	private $options;
	/** @var \Jigoshop\Service\ProductServiceInterface */
	private $productService;
	/** @var Types\Product */
	private $type;

	public function __construct(Wordpress $wp, Options $options, Types\Product $type, ProductServiceInterface $productService)
	{
		$this->wp = $wp;
		$this->options = $options;
		$this->productService = $productService;
		$this->type = $type;

		$wp->addFilter(sprintf('manage_edit-%s_columns', Types::PRODUCT), [$this, 'columns']);
		$wp->addAction(sprintf('manage_%s_posts_custom_column', Types::PRODUCT), [$this, 'displayColumn'], 2);
		$wp->addAction('restrict_manage_posts', [$this, 'categoryFilter']);
		$wp->addAction('restrict_manage_posts', [$this, 'typeFilter']);
		$wp->addAction('restrict_manage_posts', [$this, 'featuredFilter']);
		$wp->addAction('pre_get_posts', [$this, 'setTypeFilter']);
		$wp->addAction('pre_get_posts', [$this, 'setFeaturedFilter']);
		$wp->addAction('wp_ajax_jigoshop.admin.products.feature_product', [$this, 'ajaxFeatureProduct']);

		$wp->addAction('quick_edit_custom_box', function($col, $type) {
            if($type == Types::PRODUCT && $col == 'type') {
                $post = $this->wp->getGlobalPost();
                if ($post === null) {
                    return;
                }

                /** @var Product | Product\Variable $product */
                $product = $this->productService->find($post->ID);
                Render::output('admin/products/quick_edit', [
                    'product' => $product,
                ]);
            }
        }, 10, 2);

		$wp->addAction('admin_enqueue_scripts', function () use ($wp){
			if ($wp->getPostType() == Types::PRODUCT) {
				Scripts::add('jigoshop.admin.products', \JigoshopInit::getUrl().'/assets/js/admin/products.js', [
					'jquery',
					'jigoshop.helpers'
                ]);

				Styles::add('jigoshop.admin.products_list', \JigoshopInit::getUrl().'/assets/css/admin/products_list.css', ['jigoshop.admin']);

				$wp->doAction('jigoshop\admin\products\assets', $wp);
			}
		});
	}

	public function ajaxFeatureProduct()
	{
		/** @var Product $product */
		$product = $this->productService->find((int)$_POST['product_id']);
		$product->setFeatured(!$product->isFeatured());
		$this->productService->save($product);

		echo json_encode([
			'success' => true,
        ]);
		exit;
	}

	public function columns()
	{
		$columns = [
			'cb' => '<input type="checkbox" />',
			'thumbnail' => null,
			'title' => _x('Name', 'product', 'jigoshop-ecommerce'),
			'featured' => sprintf(
				'<span class="glyphicon glyphicon-star" aria-hidden="true"></span> <span class="sr-only">%s</span>',
				_x('Is featured?', 'product', 'jigoshop-ecommerce')
			),
			'type' => _x('Type', 'product', 'jigoshop-ecommerce'),
			'sku' => _x('SKU', 'product', 'jigoshop-ecommerce'),
			'stock' => _x('Stock', 'product', 'jigoshop-ecommerce'),
			'price' => _x('Price', 'product', 'jigoshop-ecommerce'),
			'creation' => _x('Created at', 'product', 'jigoshop-ecommerce'),
        ];

		// TODO: there is no option to enable/disable sku remove it or consider adding this option
//		if ($this->options->get('products.enable_sku', '1') !== '1') {
//			unset($columns['sku']);
//		}
		if ($this->options->get('products.manage_stock', '1') !== '1') {
			unset($columns['stock']);
		}

		return $columns;
	}

	public function displayColumn($column)
	{
		$post = $this->wp->getGlobalPost();
		if ($post === null) {
			return;
		}

		/** @var Product | Product\Variable $product */
		$product = $this->productService->find($post->ID);
		switch ($column) {
			case 'thumbnail':
				echo ProductHelper::getFeaturedImage($product, Options::IMAGE_THUMBNAIL);
				Render::output('admin/products/inline_product_data', [
				    'product' => $product,
                ]);
				break;
			case 'price':
				echo ProductHelper::getPriceHtml($product);
				break;
			case 'featured':
				echo ProductHelper::isFeatured($product);
				break;
			case 'type':
				echo $this->type->getType($product->getType())->getName();
				break;
			case 'sku':
				echo $this->getVariableAdditionalInfo($product, 'sku');
			break;
			case 'stock':
				echo $this->getVariableAdditionalInfo($product, 'stock');
			break;
			case 'creation':
				$timestamp = strtotime($post->post_date);
				echo Formatter::date($timestamp);

				if ($product->isVisible()) {
					echo '<br /><strong>'.__('Visible in', 'jigoshop-ecommerce').'</strong>: ';
					switch ($product->getVisibility()) {
						case ProductEntity::VISIBILITY_SEARCH:
							echo __('Search only', 'jigoshop-ecommerce');
							break;
						case ProductEntity::VISIBILITY_CATALOG:
							echo __('Catalog only', 'jigoshop-ecommerce');
							break;
						case ProductEntity::VISIBILITY_PUBLIC:
							echo __('Catalog and search', 'jigoshop-ecommerce');
							break;
					}
				}
				break;
		}
	}

	/**
	 * Filter products by category, uses slugs for option values.
	 * Props to: Andrew Benbow - chromeorange.co.uk
	 */
	public function categoryFilter()
	{
		$type = $this->wp->getTypeNow();
		if ($type != Types::PRODUCT) {
			return;
		}

		$query = [
			'pad_counts' => 1,
			'hierarchical' => true,
			'hide_empty' => true,
			'show_count' => true,
			'selected' => $this->wp->getQueryParameter(Types::PRODUCT_CATEGORY),
        ];

		$terms = $this->wp->getTerms(Types::PRODUCT_CATEGORY, $query);
		if (!$terms) {
			return;
		}

		$current = isset($_GET[Types::PRODUCT_CATEGORY]) ? $_GET[Types::PRODUCT_CATEGORY] : '';
		$walker = new \Jigoshop\Web\CategoryWalker($this->wp, 'admin/products/categoryFilter/item');

		Render::output('admin/products/categoryFilter', [
			'terms' => $terms,
			'current' => $current,
			'walker' => $walker,
			'query' => $query,
        ]);
	}

	/**
	 * Filter products by type
	 */
	public function typeFilter()
	{
		$type = $this->wp->getTypeNow();
		if ($type != Types::PRODUCT) {
			return;
		}

		// Get all active terms
		$types = [];
		foreach ($this->type->getEnabledTypes() as $type) {
			/** @var $type Types\Product\Type */
			$types[$type->getId()] = [
				'label' => $type->getName(),
				'count' => $this->getTypeCount($type),
            ];
		}
		$currentType = isset($_GET['product_type']) ? $_GET['product_type'] : '';

		Render::output('admin/products/typeFilter', [
			'types' => $types,
			'current' => $currentType
        ]);
	}

    /**
     * Filter products by type
     */
    public function featuredFilter()
    {
        $type = $this->wp->getTypeNow();
        if ($type != Types::PRODUCT) {
            return;
        }

        $current = isset($_GET['featured']) ? $_GET['featured'] : '';

        Render::output('admin/products/featuredFilter', [
            'current' => $current
        ]);
    }

	/**
	 * Finds and returns number of products of specified type.
	 *
	 * @param $type Types\Product\Type Type class.
	 *
	 * @return int Count of the products.
	 */
	private function getTypeCount($type)
	{
		$wpdb = $this->wp->getWPDB();

		return $wpdb->get_var($wpdb->prepare("
			SELECT COUNT(*) FROM {$wpdb->posts} p
				LEFT JOIN {$wpdb->postmeta} pm ON pm.post_id = p.ID
				WHERE pm.meta_key = %s AND pm.meta_value = %s
		", ['type', $type->getId()]));
	}

	/**
	 * @param $query \WP_Query
	 */
	public function setTypeFilter($query)
	{
		if (isset($_GET['product_type']) && in_array($_GET['product_type'], array_keys($this->type->getEnabledTypes()))) {
			if($query->meta_query instanceof \WP_Meta_Query) {
			    $meta = $query->meta_query->queries;
            } else {
                $meta = $query->meta_query;
            }

			$meta[] = [
				'key' => 'type',
				'value' => $_GET['product_type'],
            ];
			$query->set('meta_query', $meta);
		}
	}

    /**
     * @param $query \WP_Query
     */
    public function setFeaturedFilter($query)
    {
        if (isset($_GET['featured']) && $_GET['featured']) {
            if($query->meta_query instanceof \WP_Meta_Query) {
                $meta = $query->meta_query->queries;
            } else {
                $meta = $query->meta_query;
            }
            $meta[] = [
                'key' => 'featured',
                'value' => '1',
            ];
            $query->set('meta_query', $meta);
        }
	}

	/**
	 * Get additional information about the products of variables.
	 *
	 * @param \Jigoshop\Entity\Product\Variable $product - Product
	 * @param string $type - chose to display sku or stock information
	 *
	 * @return string
	 */
	public function getVariableAdditionalInfo($product, $type)
	{
		if ($product->getType() == Product\Variable::TYPE && $this->options->get('advanced.products_list.variations_sku_stock'))
		{
			$additionalInfo = '';
			/** @var \Jigoshop\Entity\Product\Variable\Variation $variation */
			/** @var Product\Attribute $attribute */
			foreach ($product->getVariations() as $variation)
			{
				if ($type == 'sku')
				{
					$additionalInfo .= $variation->getProduct()
					                             ->getSku() . '<br />';;
				}
				elseif ($type == 'stock')
				{
					$variation_name = [];
					$attributes = $product->getVariableAttributes();

					foreach ($attributes as $attribute)
					{
						$variation_name[] = ProductHelper::getSelectOption($attribute->getOptions())[$variation->getAttribute($attribute->getId())
						                                                                                       ->getValue()];
					}

					$additionalInfo .= join(' - ', $variation_name) . ' (' . $variation->getProduct()
					                                                                   ->getStock()
					                                                                   ->getStock() . ')<br />';
				}
			}

			return $additionalInfo;
		}
		else
		{
			if ($type == 'sku')
			{
				return $product->getSku();
			}
			elseif ($type == 'stock')
			{
				return ProductHelper::getStock($product);
			}
		}

	}
}
