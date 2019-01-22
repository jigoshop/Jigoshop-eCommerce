<?php

namespace Jigoshop\Query;

use Jigoshop\Core\Options;
use Jigoshop\Core\Types;
use Jigoshop\Entity\Order;
use Jigoshop\Entity\Product;
use Jigoshop\Frontend\Pages;
use WPAL\Wordpress;

class Interceptor
{
	private $intercepted = false;
	/** @var Wordpress */
	private $wp;
	/** @var Options */
	private $options;

	public function __construct(Wordpress $wp, Options $options)
	{
		$this->wp = $wp;
		$this->options = $options;

		$this->endpoints = [
			'edit-address',
			'change-password',
			'orders',
			'pay',
        ];
	}

	public function run()
	{
		$this->addEndpoints();
		$this->wp->addFilter('request', [$this, 'intercept']);
		$this->wp->addFilter('wp_nav_menu_objects', [$this, 'menu']);
		$this->wp->addFilter('posts_join', [$this, 'improveSearchQueryJoin'], 10, 2);
		$this->wp->addFilter('posts_where', [$this, 'improveSearchQueryWhere'], 10, 2);
	}

	/**
	 * Adds endpoints.
	 */
	public function addEndpoints()
	{
		foreach ($this->endpoints as $endpoint) {
			$this->wp->addRewriteEndpoint($endpoint, EP_ROOT | EP_PAGES | EP_PERMALINK);
		}
		//$this->wp->flushRewriteRules();
	}

	/**
	 * Updates menu items to enable "Shop" item when necessary.
	 *
	 * @param $items array Menu items.
	 *
	 * @return array Updated menu items.
	 */
	public function menu($items)
	{
		if ($this->wp->getQueryParameter('post_type', false) == Types::PRODUCT) {
			foreach ($items as $item) {
				/** @var $item \WP_Post */
				/** @noinspection PhpUndefinedFieldInspection */
				if ($item->object_id == $this->options->getPageId(Pages::SHOP)) {
					/** @noinspection PhpUndefinedFieldInspection */
					$item->classes[] = 'current-menu-item';
				}
			}
		}

		return $items;
	}

	public function intercept($request)
	{
        if ($this->intercepted) {
            return $request;
        }
        $this->intercepted = true;

		return $this->parseRequest($request);
	}

	private function parseRequest($request)
	{
        if(is_admin() == false) {
            if ($this->isProductCategory($request) || $this->isProductTag($request)) {
                $result = $this->_getProductListBaseQuery($request);
                if ($this->isProductCategory($request)) {
                    $result = $this->getProductCategoryListQuery($result, $request);
                }
                if ($this->isProductTag($request)) {
                    $result = $this->getProductTagListQuery($result, $request);
                }

                return $result;
            }

            if ($this->isProductList($request)) {
                return $this->getProductListQuery($request);
            }

            if ($this->isProduct($request)) {
                return $this->getProductQuery($request);
            }

            if ($this->isCart($request)) {
                return $this->wp->applyFilters('jigoshop\query\cart', $request, $request);
            }

            if ($this->isAccount($request)) {
                return $this->wp->applyFilters('jigoshop\query\account', $request, $request);
            }
        } else {
            if ($this->isAdminOrderList($request)) {
                return $this->getAdminOrderListQuery($request);
            }

            if ($this->isAdminProductList($request)) {
                return $this->getAdminProductListQuery($request);
            }
        }

		return $request;
	}

	private function isCart($request)
	{
		return isset($request['pagename']) && $request['pagename'] == get_post_field('post_name', $this->options->getPageId(Pages::CART));
	}

	private function isProductCategory($request)
	{
	    if(isset($request['product']) && $request['product']) {
	        return false;
        }

		return isset($request[Types\ProductCategory::NAME]) || (isset($request['taxonomy']) && $request['taxonomy'] == Types\ProductCategory::NAME);
	}

	private function getProductCategoryListQuery($result, $request)
	{
		if(isset($request[Types\ProductCategory::NAME])) {
		    $result[Types\ProductCategory::NAME] = $request[Types\ProductCategory::NAME];
        } elseif(isset($request['taxonomy']) && $request['taxonomy'] == Types\ProductCategory::NAME) {
            $result['taxonomy'] = Types\ProductCategory::NAME;
            $result['term'] = $request['term'];
        }

		return $this->wp->applyFilters('jigoshop\query\product_category_list', $result, $request);
	}

	private function _getProductListBaseQuery($request)
	{
		$options = $this->options->get('shopping');
		$result = [
			'post_type' => Types::PRODUCT,
			'post_status' => 'publish',
			'ignore_sticky_posts' => true,
			'posts_per_page' => $options['catalog_per_page'],
			'paged' => isset($request['paged']) ? $request['paged'] : 1,
			'orderby' => $options['catalog_order_by'],
			'order' => $options['catalog_order']
        ];

        if($this->options->get('advanced.ignore_meta_queries', false) == false) {
            $result['meta_query'] = [
                [
                    'key' => 'visibility',
                    'value' => [Product::VISIBILITY_CATALOG, Product::VISIBILITY_PUBLIC],
                    'compare' => 'IN'
                ]
            ];
            if ($options['hide_out_of_stock']) {
                $result['meta_query'][] = [
                    [
                        'key' => 'stock_status',
                        'value' => 1,
                        'compare' => '='
                    ],
                ];
            }
        }

        // Support for search queries
        if (isset($request['s'])) {
            $result['s'] = $request['s'];
		}

		return $this->wp->applyFilters('jigoshop\query\product_list_base', $result, $request);
	}

	private function isProductTag($request)
	{
        if(isset($request['product']) && $request['product']) {
            return false;
        }

        return isset($request[Types\ProductTag::NAME]) || (isset($request['taxonomy']) && $request['taxonomy'] == Types\ProductTag::NAME);
	}

	private function getProductTagListQuery($result, $request)
	{
        if(isset($request[Types\ProductTag::NAME])) {
            $result[Types\ProductTag::NAME] = $request[Types\ProductTag::NAME];
        } elseif(isset($request['taxonomy']) && $request['taxonomy'] == Types\ProductTag::NAME) {
            $result['taxonomy'] = Types\ProductTag::NAME;
            $result['term'] = $request['term'];
        }


        return $this->wp->applyFilters('jigoshop\query\product_tag_list', $result, $request);
	}

	private function isProductList($request)
	{
        return (!isset($request['product']) && !isset($request['preview']) && (
			(isset($request['pagename']) && $request['pagename'] == get_post_field('post_name', $this->options->getPageId(Pages::SHOP))) ||
			(isset($request['post_type']) && $request['post_type'] == Types::PRODUCT)
		)) || (empty($request) && get_option('page_on_front') == $this->options->getPageId(Pages::SHOP));
	}

	private function getProductListQuery($request)
	{
		$result = $this->_getProductListBaseQuery($request);

		return $this->wp->applyFilters('jigoshop\query\product_list', $result, $request);
	}

	private function isProduct($request)
	{
		return isset($request['post_type']) && $request['post_type'] == Types::PRODUCT;
	}

	private function getProductQuery($request)
	{
		$result = [
			'name' => isset($request['product']) ? $request['product'] : '',
			'post_type' => Types::PRODUCT,
			'post_status' => 'publish',
			'posts_per_page' => 1,
        ];

        if(isset($request['p'], $request['preview']) && $request['preview'] == "true") {
            $result = array_merge($result, $request);
            unset($result['post_status']);
        }

		return $this->wp->applyFilters('jigoshop\query\product', $result, $request);
	}

	private function isAccount($request)
	{
		return isset($request['pagename']) && $request['pagename'] == get_post_field('post_name', $this->options->getPageId(Pages::ACCOUNT));
	}

	private function isAdminOrderList($request)
    {
        return $this->wp->getPageNow() == 'edit.php' && isset($request['post_type']) && $request['post_type'] == Types::ORDER;
    }

    private function getAdminOrderListQuery($request)
    {
        $request['post_status'] = array_keys(Order\Status::getStatuses());

        return $request;
    }

    private function isAdminProductList($request)
    {
        return $this->wp->getPageNow() == 'edit.php' && isset($request['post_type']) && $request['post_type'] == Types::PRODUCT;
    }

    private function getAdminProductListQuery($request)
    {
        return $request;
    }

    /**
     * @param string $join
     * @param \WP_Query $query
     *
     * @return string
     */
    public function improveSearchQueryJoin($join, $query)
    {
        if (isset($query->query, $query->query['s'], $query->query['post_type']) &&  in_array($query->query['post_type'], [Types::ORDER, Types::PRODUCT])) {
        $wpdb = $this->wp->getWPDB();
        $fields = $this->getSearchFields($query->query['post_type']);

            for($i = 0; $i < count($fields); $i++) {
                $join .=" LEFT JOIN {$wpdb->postmeta} as jse_search_{$i} ON ({$wpdb->posts}.ID = jse_search_{$i}.post_id  AND jse_search_{$i}.meta_key = '{$fields[$i]}')";
            }
        }

        return $join;
    }

    /**
     * @param string $where
     * @param \WP_Query $query
     * @return mixed
     */
    public function improveSearchQueryWhere($where, $query)
    {
        if (isset($query->query, $query->query['s'], $query->query['post_type']) &&  in_array($query->query['post_type'], [Types::ORDER, Types::PRODUCT])) {
            $wpdb = $this->wp->getWPDB();
            $fields = $this->getSearchFields($query->query['post_type']);

            for($i = 0; $i < count($fields); $i++) {
                $where = preg_replace(
                    "/\(\s*{$wpdb->posts}.post_title\s+LIKE\s*(\'[^\']+\')\s*\)/",
                    "({$wpdb->posts}.post_title LIKE $1) OR (jse_search_{$i}.meta_value LIKE $1)", $where);
            }
        }

        return $where;
    }

    private function getSearchFields($postType)
    {
        if($postType == Types::PRODUCT) {
            return ['sku'];
        } elseif ($postType == Types::ORDER) {
            return ['number', 'customer'];
        }
    }
}
