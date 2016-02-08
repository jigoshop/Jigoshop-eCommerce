<?php
namespace Jigoshop;

use Jigoshop\Container;
use WPAL\Wordpress;

/**
 * Class Widget
 *
 * @package Jigoshop
 */
class Widget
{
	/**
	 * @var \WPAL\Wordpress
	 */
	private $wp;
	/**
	 * @var \Jigoshop\Container
	 */
	private $di;

	/**
	 * @param \Jigoshop\Container $di
	 * @param \WPAL\Wordpress $wp
	 */
	public function __construct(Container $di, Wordpress $wp)
	{
		$this->wp = $wp;
		$this->di = $di;
	}

	public function init()
	{
		$wp = $this->wp;
		$di = $this->di;
		$widgets = $this->wp->applyFilters('jigoshop\widget\init', $this->getDefaultWidgets());

		$this->wp->addAction('widgets_init', function () use ($wp, $di, $widgets){
			foreach ($widgets as $widget) {
				$class = $widget['class'];
				$wp->registerWidget($class);
				if (isset($widget['calls'])) {
					foreach ($widget['calls'] as $call) {
						list($method, $argument) = $call;
						$class::$method($di->get($argument));
					}
				}
			}
		});
	}

	/**
	 * @return array
	 */
	public function getDefaultWidgets()
	{
		return array(
			'best_seller' => array(
				'class' => '\\Jigoshop\\Widget\\BestSellers',
				'calls' => array(
					array(
						'setProductService',
						'jigoshop.service.product',
					),
				),
			),
			'cart' => array(
				'class' => '\\Jigoshop\\Widget\\Cart',
				'calls' => array(
					array(
						'setOptions',
						'jigoshop.options',
					),
					array(
						'setCart',
						'jigoshop.service.cart',
					),
				),
			),
			'featured_products' => array(
				'class' => '\\Jigoshop\\Widget\\FeaturedProducts',
				'calls' => array(
					array(
						'setProductService',
						'jigoshop.service.product',
					),
				),
			),
			'layered_nav' => array(
				'class' => '\\Jigoshop\\Widget\\LayeredNav',
				'calls' => array(
					array(
						'setProductService',
						'jigoshop.service.product',
					)
				),
			),
			'price_filter' => array(
				'class' => '\\Jigoshop\\Widget\\PriceFilter',
			),
			'product_categories' => array(
				'class' => '\\Jigoshop\\Widget\\ProductCategories',
				'calls' => array(
					array(
						'setWp',
						'wpal',
					),
					array(
						'setOptions',
						'jigoshop.options',
					),
					array(
						'setProductService',
						'jigoshop.service.product',
					),
				),
			),
			'product_search' => array(
				'class' => '\\Jigoshop\\Widget\\ProductSearch',
			),
			'products_on_sale' => array(
				'class' => '\\Jigoshop\\Widget\\ProductsOnSale',
				'calls' => array(
					array(
						'setProductService',
						'jigoshop.service.product',
					),
				),
			),
			'product_tag_cloud' => array(
				'class' => '\\Jigoshop\\Widget\\ProductTagCloud',
			),
			'random_products' => array(
				'class' => '\\Jigoshop\\Widget\\RandomProducts',
				'calls' => array(
					array(
						'setProductService',
						'jigoshop.service.product',
					),
				),
			),
			'recently_viewed_products' => array(
				'class' => '\\Jigoshop\\Widget\\RecentlyViewedProducts',
				'calls' => array(
					array(
						'setProductService',
						'jigoshop.service.product',
					),
				),
			),
			'recent_products' => array(
				'class' => '\\Jigoshop\\Widget\\RecentProducts',
				'calls' => array(
					array(
						'setProductService',
						'jigoshop.service.product',
					),
				),
			),
			'recent_reviews' => array(
				'class' => '\\Jigoshop\\Widget\\RecentReviews',
				'calls' => array(
					array(
						'setProductService',
						'jigoshop.service.product',
					),
				),
			),
			'top_rated' => array(
				'class' => '\\Jigoshop\\Widget\\TopRated',
				'calls' => array(
					array(
						'setProductService',
						'jigoshop.service.product',
					),
				),
			),
			'user_login' => array(
				'class' => '\\Jigoshop\\Widget\\UserLogin',
				'calls' => array(
					array(
						'setOptions',
						'jigoshop.options',
					),
				),
			),
		);
	}
}