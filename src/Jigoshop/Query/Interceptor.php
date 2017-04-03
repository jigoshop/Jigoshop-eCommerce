<?php

namespace Jigoshop\Query;

use Jigoshop\Core\Options;
use Jigoshop\Core\Types;
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

		$this->endpoints = array(
			'edit-address',
			'change-password',
			'orders',
			'pay',
		);
	}

	public function run()
	{
		$this->addEndpoints();
		$this->wp->addFilter('request', array($this, 'intercept'));
		$this->wp->addFilter('wp_nav_menu_objects', array($this, 'menu'));
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
            if ($this->isCart($request)) {
                return $this->wp->applyFilters('jigoshop\query\cart', $request, $request);
            }

            if ($this->isProductCategory($request)) {
                return $this->getProductCategoryListQuery($request);
            }

            if ($this->isProductTag($request)) {
                return $this->getProductTagListQuery($request);
            }

            if ($this->isProductList($request)) {
                return $this->getProductListQuery($request);
            }

            if ($this->isProduct($request)) {
                return $this->getProductQuery($request);
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
		return isset($request[Types\ProductCategory::NAME]);
	}

	private function getProductCategoryListQuery($request)
	{
		$result = $this->_getProductListBaseQuery($request);
		$result[Types\ProductCategory::NAME] = $request[Types\ProductCategory::NAME];

		return $this->wp->applyFilters('jigoshop\query\product_category_list', $result, $request);
	}

	private function _getProductListBaseQuery($request)
	{
		$options = $this->options->get('shopping');
		$result = array(
			'post_type' => Types::PRODUCT,
			'post_status' => 'publish',
			'ignore_sticky_posts' => true,
			'posts_per_page' => $options['catalog_per_page'],
			'paged' => isset($request['paged']) ? $request['paged'] : 1,
			'orderby' => $options['catalog_order_by'],
			'order' => $options['catalog_order']
		);

        if($this->options->get('advanced.ignore_meta_queries', false) == false) {
            $result['meta_query'] = array(
                array(
                    'key' => 'visibility',
                    'value' => array(Product::VISIBILITY_CATALOG, Product::VISIBILITY_PUBLIC),
                    'compare' => 'IN'
                )
            );
            if ($options['hide_out_of_stock'] == 'on') {
                $result['meta_query'][] = array(
                    array(
                        'key' => 'stock_status',
                        'value' => 1,
                        'compare' => '='
                    ),
                );
            }
        }

        // Support for search queries
        if (isset($request['s'])) {
            $wpdb = $this->wp->getWPDB();

            $result['s'] = $request['s'];

            $joinClosure = function($join) use ($wpdb, &$joinClosure) {
                if ( is_search() ) {
                    $join .=' LEFT JOIN '.$wpdb->postmeta. ' as jse_search ON ('. $wpdb->posts . '.ID = jse_search.post_id  AND jse_search.meta_key = "sku")';
                }

                remove_filter('posts_join', $joinClosure);
                return $join;
            };

            $whereClosure = function($where) use ($wpdb, &$whereClosure) {
                if ( is_search() ) {
                    $where = preg_replace(
                        "/\(\s*".$wpdb->posts.".post_title\s+LIKE\s*(\'[^\']+\')\s*\)/",
                        "(".$wpdb->posts.".post_title LIKE $1) OR (jse_search.meta_value LIKE $1)", $where );
                }

                remove_filter('posts_where', $whereClosure);
                return $where;
            };

            add_filter('posts_join', $joinClosure);
            add_filter('posts_where', $whereClosure);
		}

		return $this->wp->applyFilters('jigoshop\query\product_list_base', $result, $request);
	}

	private function isProductTag($request)
	{
		return isset($request[Types\ProductTag::NAME]);
	}

	private function getProductTagListQuery($request)
	{
		$result = $this->_getProductListBaseQuery($request);
		$result[Types\ProductTag::NAME] = $request[Types\ProductTag::NAME];

		return $this->wp->applyFilters('jigoshop\query\product_tag_list', $result, $request);
	}

	private function isProductList($request)
	{
		return !isset($request['product']) && !isset($request['preview']) && (
			(isset($request['pagename']) && $request['pagename'] == get_post_field('post_name', $this->options->getPageId(Pages::SHOP))) ||
			(isset($request['post_type']) && $request['post_type'] == Types::PRODUCT)
		);
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
		$result = array(
			'name' => isset($request['product']) ? $request['product'] : '',
			'post_type' => Types::PRODUCT,
			'post_status' => 'publish',
			'posts_per_page' => 1,
		);

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
        if(isset($request['s']) && $request['s']) {
            $wpdb = $this->wp->getWPDB();
            $ids = $wpdb->get_results($wpdb->prepare("SELECT posts.ID as ID FROM {$wpdb->posts} as posts
                INNER JOIN {$wpdb->postmeta} as meta ON (meta.post_id = posts.ID AND meta.meta_key = 'number')
                INNER JOIN {$wpdb->postmeta} as meta2 ON (meta2.post_id = posts.ID AND meta2.meta_key = 'customer')
                WHERE meta.meta_value LIKE %s OR meta2.meta_value LIKE %s OR posts.ID = %d",
                '%'.$request['s'].'%', '%:"%'.$request['s'].'%";%', $request['s']), ARRAY_A);

            unset($request['s']);
            unset($request['m']);
            $request['post__in'] = array_map(function($item){ return $item['ID']; }, $ids);
            $request['post__in'] = count($request['post__in']) ? $request['post__in'] : [0];
        }

        return $request;
    }

    private function isAdminProductList($request)
    {
        return $this->wp->getPageNow() == 'edit.php' && isset($request['post_type']) && $request['post_type'] == Types::PRODUCT;
    }

    private function getAdminProductListQuery($request)
    {
        if(isset($request['s']) && $request['s']) {
            $wpdb = $this->wp->getWPDB();
            $ids = $wpdb->get_results($wpdb->prepare("SELECT posts.ID as ID FROM {$wpdb->posts} as posts
                INNER JOIN {$wpdb->postmeta} as meta ON (meta.post_id = posts.ID AND meta.meta_key = 'sku')
                WHERE meta.meta_value LIKE %s OR posts.post_title LIKE %s OR posts.post_content LIKE %s OR posts.ID = %d",
                '%'.$request['s'].'%', '%'.$request['s'].'%', '%'.$request['s'].'%', $request['s']), ARRAY_A);

            unset($request['s']);
            unset($request['m']);
            $request['post__in'] = array_map(function($item){ return $item['ID']; }, $ids);
            $request['post__in'] = count($request['post__in']) ? $request['post__in'] : [0];
        }

        return $request;
    }
}
