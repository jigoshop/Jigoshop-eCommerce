<?php
namespace Jigoshop;

use Jigoshop\Admin\Settings\LayoutTab;
use Jigoshop\Container;
use Jigoshop\Core\Options;
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
			/** @var Options $options */
			$options = $di->get('jigoshop.options');
			$settings = $options->get(LayoutTab::SLUG);
			unset($settings['enabled'], $settings['page_width'], $settings['global_css']);
			$sidebars = [];
			foreach($settings as $pageSettings) {
			    if((!isset($pageSettings['enabled']) || $pageSettings['enabled']) && $pageSettings['structure'] != 'only_content') {
			        $sidebars[] = $pageSettings['sidebar'];
                }
            }
            foreach(array_unique($sidebars, SORT_NUMERIC) as $sidebar) {
			    register_sidebar([
                    'id' => 'jigoshop_sidebar_'.$sidebar,
                    'name' => sprintf(__('Jigoshop Sidebar %d', 'textdomain'), $sidebar),
                    //'description' => __( 'A short description of the sidebar.', 'textdomain' ),
                    'before_widget' => '<aside id="%1$s" class="widget %2$s">',
                    'after_widget' => '</aside>',
                    'before_title' => '<h3 class="widget-title">',
                    'after_title' => '</h3>'
                ]);
            }
        });
	}

	/**
	 * @return array
	 */
	public function getDefaultWidgets()
	{
		return [
			'best_seller' => [
				'class' => '\\Jigoshop\\Widget\\BestSellers',
				'calls' => [
					[
						'setProductService',
						'jigoshop.service.product',
                    ],
                ],
            ],
			'cart' => [
				'class' => '\\Jigoshop\\Widget\\Cart',
				'calls' => [
					[
						'setOptions',
						'jigoshop.options',
                    ],
					[
						'setCart',
						'jigoshop.service.cart',
                    ],
                ],
            ],
			'featured_products' => [
				'class' => '\\Jigoshop\\Widget\\FeaturedProducts',
				'calls' => [
					[
						'setProductService',
						'jigoshop.service.product',
                    ],
                ],
            ],
			'layered_nav' => [
				'class' => '\\Jigoshop\\Widget\\LayeredNav',
				'calls' => [
					[
						'setProductService',
						'jigoshop.service.product',
                    ]
                ],
            ],
			'price_filter' => [
				'class' => '\\Jigoshop\\Widget\\PriceFilter',
            ],
			'product_categories' => [
				'class' => '\\Jigoshop\\Widget\\ProductCategories',
				'calls' => [
					[
						'setWp',
						'wpal',
                    ],
					[
						'setOptions',
						'jigoshop.options',
                    ],
					[
						'setProductService',
						'jigoshop.service.product',
                    ],
                ],
            ],
			'product_search' => [
				'class' => '\\Jigoshop\\Widget\\ProductSearch',
            ],
			'products_on_sale' => [
				'class' => '\\Jigoshop\\Widget\\ProductsOnSale',
				'calls' => [
					[
						'setProductService',
						'jigoshop.service.product',
                    ],
                ],
            ],
			'product_tag_cloud' => [
				'class' => '\\Jigoshop\\Widget\\ProductTagCloud',
            ],
			'random_products' => [
				'class' => '\\Jigoshop\\Widget\\RandomProducts',
				'calls' => [
					[
						'setProductService',
						'jigoshop.service.product',
                    ],
                ],
            ],
			'recently_viewed_products' => [
				'class' => '\\Jigoshop\\Widget\\RecentlyViewedProducts',
				'calls' => [
					[
						'setProductService',
						'jigoshop.service.product',
                    ],
                ],
            ],
			'recent_products' => [
				'class' => '\\Jigoshop\\Widget\\RecentProducts',
				'calls' => [
					[
						'setProductService',
						'jigoshop.service.product',
                    ],
                ],
            ],
			'recent_reviews' => [
				'class' => '\\Jigoshop\\Widget\\RecentReviews',
				'calls' => [
					[
						'setProductService',
						'jigoshop.service.product',
                    ],
                ],
            ],
			'top_rated' => [
				'class' => '\\Jigoshop\\Widget\\TopRated',
				'calls' => [
					[
						'setProductService',
						'jigoshop.service.product',
                    ],
                ],
            ],
			'user_login' => [
				'class' => '\\Jigoshop\\Widget\\UserLogin',
				'calls' => [
					[
						'setOptions',
						'jigoshop.options',
                    ],
                ],
            ],
        ];
	}
}