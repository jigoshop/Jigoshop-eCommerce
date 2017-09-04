<?php

namespace Jigoshop\Admin;

use Jigoshop\Admin;
use Jigoshop\Core\Options;
use Jigoshop\Core\Types;
use Jigoshop\Entity\Order;
use Jigoshop\Helper\Render;
use Jigoshop\Helper\Scripts;
use Jigoshop\Helper\Styles;
use Jigoshop\Service\OrderServiceInterface;
use Jigoshop\Service\ProductServiceInterface;
use WPAL\Wordpress;

/**
 * Jigoshop dashboard.
 *
 * @package Jigoshop\Admin
 * @author  Amadeusz Starzykiewicz
 */
class Dashboard implements PageInterface
{
	const NAME = 'jigoshop';

	/** @var Wordpress */
	private $wp;
	/** @var \Jigoshop\Service\OrderServiceInterface */
	private $orderService;
	/** @var \Jigoshop\Service\ProductServiceInterface */
	private $productService;
	/** @var Options */
	private $options;

	public function __construct(Wordpress $wp, Options $options, OrderServiceInterface $orderService, ProductServiceInterface $productService)
	{
		$this->wp = $wp;
		$this->options = $options;
		$this->orderService = $orderService;
		$this->productService = $productService;

		$wp->addAction('admin_enqueue_scripts', function () use ($wp){
			// Weed out all admin pages except the Jigoshop Settings page hits
			if (!in_array($wp->getPageNow(), ['admin.php', 'options.php'])) {
				return;
			}

			$screen = $wp->getCurrentScreen();
			if (!in_array($screen->base, ['toplevel_page_'.Dashboard::NAME, 'options'])) {
				return;
			}

			Styles::add('jigoshop.admin.dashboard', \JigoshopInit::getUrl().'/assets/css/admin/dashboard.css');
            Scripts::add('jigoshop.vendors.flot', \JigoshopInit::getUrl().'/assets/js/vendors/flot.js', ['jquery']);
		});
	}

	/** @return string Title of page. */
	public function getTitle()
	{
		return __('Dashboard', 'jigoshop-ecommerce');
	}

	/** @return string Parent of the page string. */
	public function getParent()
	{
		return Admin::MENU;
	}

	/** @return string Required capability to view the page. */
	public function getCapability()
	{
		return 'manage_jigoshop';
	}

	/** @return string Menu slug. */
	public function getMenuSlug()
	{
		return self::NAME;
	}

	/** Displays the page. */
	public function display()
	{
		$this->wp->wpEnqueueScript('common');
		$this->wp->wpEnqueueScript('wp-lists');
		$this->wp->wpEnqueueScript('postbox');
		Styles::add('wp-jquery-ui');

		$this->wp->addMetaBox('jigoshop_dashboard_right_now', __('<span>Shop</span> Content', 'jigoshop-ecommerce'), [$this, 'rightNow'], 'jigoshop', 'side', 'core');
		$this->wp->addMetaBox('jigoshop_dashboard_recent_orders', __('<span>Recent</span> Orders', 'jigoshop-ecommerce'), [$this, 'recentOrders'], 'jigoshop', 'side', 'core');
		if ($this->options->get('products.manage_stock')) {
			$this->wp->addMetaBox('jigoshop_dashboard_stock_report', __('<span>Stock</span> Report', 'jigoshop-ecommerce'), [$this, 'stockReport'], 'jigoshop', 'side', 'core');
		}
		$this->wp->addMetaBox('jigoshop_dashboard_monthly_report', __('<span>Monthly</span> Report', 'jigoshop-ecommerce'), [$this, 'monthlyReport'], 'jigoshop', 'normal', 'core');
		$this->wp->addMetaBox('jigoshop_dashboard_recent_reviews', __('<span>Recent</span> Reviews', 'jigoshop-ecommerce'), [$this, 'recentReviews'], 'jigoshop', 'normal', 'core');
		$this->wp->addMetaBox('jigoshop_dashboard_latest_news', __('<span>Latest</span> News', 'jigoshop-ecommerce'), [$this, 'latestNews'], 'jigoshop', 'normal', 'core');
		$this->wp->addMetaBox('jigoshop_dashboard_useful_links', __('<span>Useful</span> Links', 'jigoshop-ecommerce'), [$this, 'usefulLinks'], 'jigoshop', 'normal', 'core');

		$submenu = $this->wp->getSubmenu();

		Render::output('admin/dashboard', [
			'submenu' => $submenu,
        ]);
	}

	/**
	 * Displays "Right Now" meta box.
	 */
	public function rightNow()
	{
		$counts = $this->wp->wpCountPosts(Types::PRODUCT);
		$productCount = $counts->publish;
		$categoryCount = $this->wp->wpCountTerms(Types::PRODUCT_CATEGORY);
		$tagCount = $this->wp->wpCountTerms(Types::PRODUCT_TAG);
		$attributesCount = $this->productService->countAttributes();
		$counts = $this->wp->wpCountPosts(Types::ORDER);
		$pendingCount = $counts->{Order\Status::PENDING};
		$onHoldCount = $counts->{Order\Status::ON_HOLD};
		$processingCount = $counts->{Order\Status::PROCESSING};
		$completedCount = $counts->{Order\Status::COMPLETED};
		$cancelledCount = $counts->{Order\Status::CANCELLED};
		$refundedCount = $counts->{Order\Status::REFUNDED};

		Render::output('admin/dashboard/rightNow', [
			'productCount' => $productCount,
			'categoryCount' => $categoryCount,
			'tagCount' => $tagCount,
			'attributesCount' => $attributesCount,
			'pendingCount' => $pendingCount,
			'onHoldCount' => $onHoldCount,
			'processingCount' => $processingCount,
			'completedCount' => $completedCount,
			'cancelledCount' => $cancelledCount,
			'refundedCount' => $refundedCount,
        ]);
	}

	/**
	 * Displays "Recent Orders" meta box.
	 */
	public function recentOrders()
	{
		/** @noinspection PhpUnusedLocalVariableInspection */
		$statuses = Order\Status::getStatuses();
		unset($statuses[Order\Status::CANCELLED], $statuses[Order\Status::REFUNDED]);
		$orders = $this->orderService->findByQuery(new \WP_Query([
			'numberposts' => 10,
			'orderby' => 'post_date',
			'order' => 'DESC',
			'post_type' => Types::ORDER,
			'post_status' => array_keys($statuses),
        ]));

		Render::output('admin/dashboard/recentOrders', [
			'orders' => $orders,
        ]);
	}

	/**
	 * Displays "Stock Report" meta box.
	 */
	public function stockReport()
	{
		$lowStockThreshold = $this->options->get('advanced.low_stock_threshold', 2);
		$notifyOufOfStock = $this->options->get('advanced.notify_out_of_stock', true);
		$number = $this->options->get('advanced.dashboard_stock_number', 5);
		$outOfStock = [];

		if ($notifyOufOfStock) {
			$outOfStock = $this->productService->findOutOfStock($number);
		}

		$lowStock = $this->productService->findLowStock($lowStockThreshold, $number);

		Render::output('admin/dashboard/stockReport', [
			'notifyOutOfStock' => $notifyOufOfStock,
			'outOfStock' => $outOfStock,
			'lowStock' => $lowStock,
        ]);
	}

	/**
	 * Displays "Monthly Report" meta box.
	 */
	public function monthlyReport()
	{
		$currentMonth = intval(date('m'));
		$currentYear = intval(date('Y'));
		$currentDay = intval(date('d'));
		$selectedMonth = isset($_GET['month']) ? intval($_GET['month']) : $currentMonth;
		$selectedYear = isset($_GET['year']) ? intval($_GET['year']) : $currentYear;
		$nextYear = ($selectedMonth == 12) ? $selectedYear + 1 : $selectedYear;
		$nextMonth = ($selectedMonth == 12) ? 1 : $selectedMonth + 1;
		$previousYear = ($selectedMonth == 1) ? $selectedYear - 1 : $selectedYear;
		$previousMonth = ($selectedMonth == 1) ? 12 : $selectedMonth - 1;

		$currentTime = strtotime($selectedYear.'-'.$selectedMonth.'-1');

		if ($currentTime >= strtotime($currentYear.'-'.$currentMonth.'-1') &&
            (strtotime($currentYear.'-'.$currentMonth.'-'.($currentDay + 1)) - $currentTime) > 24 * 3600) {
			$days = range($currentTime, strtotime($currentYear.'-'.$currentMonth.'-'.$currentDay), 24 * 3600);
		} else {
			$days = range($currentTime, strtotime($nextYear.'-'.$nextMonth.'-1'), 24 * 3600);
		}
        $orders = $this->orderService->findFromMonth($selectedMonth, $selectedYear);


		$orderData = $this->getOrderData($orders, $days);
		$orderAmounts = $orderCounts = [];

		foreach ($days as $day) {
			$orderCounts[] = [$day, $orderData['counts'][$day]];
            $orderAmounts[] = [$day, $orderData['amounts'][$day]];
        }

		Render::output('admin/dashboard/monthlyReport', [
			'orders' => $orders,
			'selectedMonth' => $selectedMonth,
			'selectedYear' => $selectedYear,
			'currentMonth' => $currentMonth,
			'currentYear' => $currentYear,
			'nextMonth' => $nextMonth,
			'nextYear' => $nextYear,
			'previousMonth' => $previousMonth,
			'previousYear' => $previousYear,
			'orderCounts' => $orderCounts,
			'orderAmounts' => $orderAmounts,
        ]);
	}

    /**
     * @param Order[] $orders
     * @param array $days
     *
     * @return array
     */
    private function getOrderData($orders, $days)
    {
        $orderCountsData = $orderAmountsData = array_fill_keys($days, 0);

        foreach ($orders as $order) {
            $day = strtotime($order->getCreatedAt()->format('Y-m-d'));
            if(!isset($orderCountsData[$day])) {
                $orderCountsData[$day] = $orderAmountsData[$day] = 0;
            }

            $orderCountsData[$day] += 1;
            $orderAmountsData[$day] += $order->getSubtotal() + $order->getShippingPrice();
        }

        return ['counts' => $orderCountsData, 'amounts' => $orderAmountsData];
	}

	/**
	 * Displays "Recent Reviews" meta box.
	 */
	public function recentReviews()
	{
		$wpdb = $this->wp->getWPDB();
		/** @noinspection PhpUnusedLocalVariableInspection */
		$comments = $wpdb->get_results("SELECT *, SUBSTRING(comment_content,1,100) AS comment_excerpt
				FROM $wpdb->comments
				LEFT JOIN $wpdb->posts ON ($wpdb->comments.comment_post_ID = $wpdb->posts.ID)
				WHERE comment_approved = '1'
				AND comment_type = ''
				AND post_password = ''
				AND post_type = 'product'
				ORDER BY comment_date_gmt DESC
				LIMIT 5");

		Render::output('admin/dashboard/recentReviews', [
			'comments' => $comments,
        ]);
	}

	/**
	 * Displays "Latest News" meta box.
	 */
	public function latestNews()
	{
		if (file_exists(ABSPATH.WPINC.'/class-simplepie.php')) {
			include_once(ABSPATH.WPINC.'/class-simplepie.php');

			$wp = $this->wp;
			$rss = $wp->fetchFeed('http://www.jigoshop.com/feed');
			$items = [];

			if (!$wp->isWpError($rss)) {
				$maxItems = $rss->get_item_quantity(5);
				$rssItems = $rss->get_items(0, $maxItems);

				if ($maxItems > 0) {
					$items = array_map(function ($item) use ($wp){
						/** @var $item \SimplePie_Item */
						$date = $item->get_date('U');

						return [
							'title' => $wp->getHelpers()->wptexturize($item->get_title()),
							'link' => $item->get_permalink(),
							'date' => (abs(time() - $date)) < 86400 ? sprintf(__('%s ago', 'jigoshop-ecommerce'), $wp->humanTimeDiff($date)) : date(__('F jS Y', 'jigoshop-ecommerce'), $date),
                        ];
					}, $rssItems);
				}
			}

			Render::output('admin/dashboard/latestNews', [
				'items' => $items,
            ]);
		}
	}

	/**
	 * Displays "Useful Links" meta box.
	 */
	public function usefulLinks()
	{
		Render::output('admin/dashboard/usefulLinks', []);
	}
}
