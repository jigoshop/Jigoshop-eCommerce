<?php

namespace Jigoshop\Admin\Reports\Chart;

use Jigoshop\Admin\Reports;
use Jigoshop\Admin\Reports\Chart;
use Jigoshop\Core\Options;
use Jigoshop\Helper\Currency;
use Jigoshop\Helper\Product;
use Jigoshop\Helper\Render;
use Jigoshop\Helper\Scripts;
use Jigoshop\Helper\Styles;
use WPAL\Wordpress;

class DiscountSummary extends Chart
{
	/** @var array */
	public $chartColours = array();
	/** @var array */
	public $couponCodes = array();
	/** @var */
	private $reportData;

	/**
	 * @param Wordpress $wp
	 * @param Options   $options
	 * @param string    $currentRange
	 */
	public function __construct(Wordpress $wp, Options $options, $currentRange)
	{
		parent::__construct($wp, $options, $currentRange);
		if (isset($_GET['coupon_codes']) && is_array($_GET['coupon_codes'])) {
			$this->couponCodes = array_filter(array_map('sanitize_text_field', $_GET['coupon_codes']));
		} elseif (isset($_GET['coupon_codes'])) {
			$this->couponCodes = array_filter(array(sanitize_text_field($_GET['coupon_codes'])));
		}

		// Prepare data for report
		$this->calculateCurrentRange();
		$this->getReportData();
		$this->getChartColours();

		$wp->addAction('admin_enqueue_scripts', function () use ($wp){
			// Weed out all admin pages except the Jigoshop Settings page hits
			if (!in_array($wp->getPageNow(), array('admin.php', 'options.php'))) {
				return;
			}

			$screen = $wp->getCurrentScreen();
			if ($screen->base != 'jigoshop_page_'.Reports::NAME) {
				return;
			}
			Styles::add('jigoshop.vendors.select2', JIGOSHOP_URL.'/assets/css/vendors/select2.min.css', array('jigoshop.admin'));
			Scripts::add('jigoshop.vendors.select2', JIGOSHOP_URL.'/assets/js/vendors/select2.min.js', array('jigoshop.admin'), array('in_footer' => true));
			Scripts::localize('jigoshop.reports.chart', 'chart_data', $this->getMainChart());
		});
	}

	/**
	 * @return array
	 */
	public function getChartLegend()
	{
		$legend = array();

		$this->getReportData();
		$totalDiscount = 0;
		$totalCoupons = 0;
		foreach ($this->reportData->orderCoupons as $order) {
			$totalDiscount += array_sum(array_map(function ($coupon){
				return $coupon['amount'];
			}, $order->coupons));
			$totalCoupons += array_sum(array_map(function ($coupon){
				return $coupon['usage'];
			}, $order->coupons));
		}

		$legend[] = array(
			'title' => sprintf(__('%s discounts in total', 'jigoshop'), '<strong>'.Product::formatPrice($totalDiscount).'</strong>'),
			'color' => $this->chartColours['discount_amount'],
			'highlight_series' => 1
		);

		$legend[] = array(
			'title' => sprintf(__('%s coupons used in total', 'jigoshop'), '<strong>'.$totalCoupons.'</strong>'),
			'color' => $this->chartColours['coupon_count'],
			'highlight_series' => 0
		);

		return $legend;
	}

	/**
	 *
	 */
	public function display()
	{
		/** @noinspection PhpUnusedLocalVariableInspection */
		$ranges = array(
			'all' => __('All Time', 'jigoshop'),
			'year' => __('Year', 'jigoshop'),
			'last_month' => __('Last Month', 'jigoshop'),
			'month' => __('This Month', 'jigoshop'),
			'30day' => __('Last 30 Days', 'jigoshop'),
			'7day' => __('Last 7 Days', 'jigoshop'),
			'today' => __('Today', 'jigoshop'),
		);

		Render::output('admin/reports/chart', array(
			/** TODO This is ugly... */
			'current_tab' => Reports\SalesTab::SLUG,
			'current_type' => 'discount_summary',
			'ranges' => $ranges,
			'url' => remove_query_arg(array('start_date', 'end_date')),
			'current_range' => $this->currentRange,
			'legends' => $this->getChartLegend(),
			'widgets' => $this->getChartWidgets(),
			'group_by' => $this->chartGroupBy
		));
	}

	/**
	 * @return array
	 */
	public function getChartWidgets()
	{
		$widgets = array();
		$usedCoupons = $this->getUsedCoupons();

		$mostDiscount = $usedCoupons;
		$mostPopular = $usedCoupons;
		usort($mostDiscount, function ($a, $b){
			return $b['amount'] - $a['amount'];
		});
		$mostDiscount = array_slice($mostDiscount, 0, 12);
		usort($mostPopular, function ($a, $b){
			return $b['usage'] - $a['usage'];
		});
		$mostPopular = array_slice($mostPopular, 0, 12);

		$widgets[] = new Chart\Widget\SelectCoupons($this->couponCodes, $usedCoupons);
		$widgets[] = new Chart\Widget\CustomRange();
		if (!empty($mostPopular)) {
			$widgets[] = new Chart\Widget\MostPopular($mostPopular);
		}
		if (!empty($mostDiscount)) {
			$widgets[] = new Chart\Widget\MostDiscount($mostDiscount);
		}

		return $widgets;
	}

	/**
	 * Get report data
	 *
	 * @return array
	 */
	public function getReportData()
	{
		if (empty($this->reportData)) {
			$this->queryReportData();
		}

		return $this->reportData;
	}

	/**
	 * Get all data needed for this report and store in the class
	 */
	private function queryReportData()
	{
		$this->reportData = new \stdClass();
		$wpdb = $this->wp->getWPDB();

		$query = $this->prepareQuery(array(
			'select' => array(
				'discount' => array(
					array(
						'field' => 'meta_value',
						'function' => '',
						'name' => 'discount'
					),
					array(
						'field' => 'post_id',
						'function' => '',
						'name' => 'order_id'
					),
				),
				'coupons' => array(
					array(
						'field' => 'meta_value',
						'function' => '',
						'name' => 'coupons'
					)
				),
				'posts' => array(
					array(
						'field' => 'post_date',
						'function' => '',
						'name' => 'post_date'
					)
				),
			),
			'from' => array(
				'discount' => $wpdb->postmeta,
			),
			'join' => array(
				'coupons' => array(
					'table' => $wpdb->postmeta,
					'on' => array(
						array(
							'key' => 'post_id',
							'value' => 'discount.post_id',
							'compare' => '=',
						),
						array(
							'key' => 'meta_key',
							'value' => '"coupons"',
							'compare' => '=',
						)
					),
				),
				'posts' => array(
					'table' => $wpdb->posts,
					'on' => array(
						array(
							'key' => 'ID',
							'value' => 'discount.post_id',
							'compare' => '=',
						)
					),
				)
			),
			'where' => array(
				array(
					'key' => 'discount.meta_key',
					'value' => '"discount"',
					'compare' => '='
				),
				array(
					'key' => 'discount.meta_value',
					'value' => '0',
					'compare' => '>'
				),
			),
			'filter_range' => true,
		));
		$coupons = $this->getOrderReportData($query);
		$this->reportData->orders = $this->parseReportData($coupons);

		$this->reportData->usedCoupons = array();
		foreach ($this->reportData->orders as $order) {
			$this->reportData->usedCoupons[$order->post_date] = new \stdClass();
			$this->reportData->usedCoupons[$order->post_date]->post_date = $order->post_date;
			$this->reportData->usedCoupons[$order->post_date]->coupons = array();
			$this->reportData->usedCoupons[$order->post_date]->usage = array();

			foreach ($order->coupons as $code => $coupon) {
				$this->reportData->usedCoupons[$order->post_date]->coupons[] = array(
					'code' => $code,
					'amount' => $coupon['amount'],
					'usage' => $coupon['usage'],
				);
				$this->reportData->usedCoupons[$order->post_date]->usage[$code] = $coupon['usage'];
			}
		}

		$couponCodes = $this->couponCodes;
		if (!empty($couponCodes[0])) {
			$this->reportData->orderCoupons = array_filter($this->reportData->usedCoupons, function ($item) use ($couponCodes){
				foreach ($couponCodes as $couponCode) {
					if (isset($item->usage[$couponCode])) {
						return true;
					}
				}

				return false;
			});
		} else {
			$this->reportData->orderCoupons = $this->reportData->usedCoupons;
		}

		$this->reportData->orderCouponCounts = array_map(function ($item) use ($couponCodes){
			$time = new \stdClass();
			$time->post_date = $item->post_date;
			if (!empty($couponCodes)) {
				foreach ($couponCodes as $couponCode) {
					if (isset($item->usage[$couponCode])) {
						$time->order_coupon_count = $item->usage[$couponCode];
					}
				}
			} else {
				$time->order_coupon_count = array_sum($item->usage);
			}

			return $time;
		}, $this->reportData->orderCoupons);

		$this->reportData->orderDiscountAmounts = array_map(function ($item) use ($couponCodes){
			$time = new \stdClass();
			$time->post_date = $item->post_date;
			if (!empty($item->coupons)) {
				$time->discount_amount = array_sum(array_map(function ($innerItem) use ($item, $couponCodes){
					if (empty($innerItem)) {
						return 0;
					}
					if (!empty($couponCodes)) {
						foreach ($couponCodes as $couponCode) {
							if ($couponCode == $innerItem['code']) {
								return $innerItem['amount'];
							}
						}

						return 0;
					} else {
						return $innerItem['amount'];
					}
				}, $item->coupons));
			} else {
				$time->discount_amount = 0;
			}

			return $time;
		}, $this->reportData->orderCoupons);
	}

	/**
	 * @return array
	 */
	public function getExportButton()
	{
		return array(
			'download' => 'report-'.esc_attr($this->currentRange).'-'.date_i18n('Y-m-d', current_time('timestamp')).'.csv',
			'xaxes' => __('Date', 'jigoshop'),
			'groupby' => $this->chartGroupBy,
		);
	}

	/**
	 * @return array
	 */
	public function getMainChart()
	{
		global $wp_locale;

		$startTime = $this->range['start'];
		$endTime = $this->range['end'];
		$filterTimes = function ($item) use ($startTime, $endTime){
			$time = strtotime($item->post_date);

			return $time >= $startTime && $time < $endTime;
		};

		// Prepare data for report
		$orderCouponCounts = $this->prepareChartData(array_filter($this->reportData->orderCouponCounts, $filterTimes), 'post_date', 'order_coupon_count', $this->chartInterval, $this->range['start'], $this->chartGroupBy);
		$orderDiscountAmounts = $this->prepareChartData(array_filter($this->reportData->orderDiscountAmounts, $filterTimes), 'post_date', 'discount_amount', $this->chartInterval, $this->range['start'], $this->chartGroupBy);

		$data = array();
		$data['series'] = array();
		$data['series'][] = $this->arrayToObject(array(
			'label' => esc_js(__('Number of coupons used', 'jigoshop')),
			'data' => array_values($orderCouponCounts),
			'color' => $this->chartColours['coupon_count'],
			'bars' => $this->arrayToObject(array(
				'fillColor' => $this->chartColours['coupon_count'],
				'fill' => true,
				'show' => true,
				'lineWidth' => 0,
				'align' => 'center',
				'barWidth' => $this->barwidth * 0.8
			)),
			'shadowSize' => 0,
			'hoverable' => false
		));
		$data['series'][] = $this->arrayToObject(array(
			'label' => esc_js(__('Discount amount', 'jigoshop')),
			'data' => array_values($orderDiscountAmounts),
			'yaxis' => 2,
			'color' => $this->chartColours['discount_amount'],
			'points' => $this->arrayToObject(array(
				'show' => true,
				'radius' => 5,
				'lineWidth' => 4,
				'fillColor' => '#fff',
				'fill' => true,
			)),
			'lines' => $this->arrayToObject(array(
				'show' => true,
				'lineWidth' => 4,
				'fill' => false,
			)),
			'shadowSize' => 0,
			'append_tooltip' => Currency::symbol(),
		));

		$data['options'] = $this->arrayToObject(array(
			'legend' => $this->arrayToObject(array(
				'show' => false,
			)),
			'grid' => $this->arrayToObject(array(
				'color' => '#aaa',
				'borderColor' => 'transparent',
				'borderWidth' => 0,
				'hoverable' => true
			)),
			'xaxes' => array(
				$this->arrayToObject(array(
					'color' => '#aaa',
					'position' => 'bottom',
					'tickColor' => 'transparent',
					'mode' => 'time',
					'timeformat' => $this->chartGroupBy == 'hour' ? '%H' : $this->chartGroupBy == 'day' ? '%d %b' : '%b',
					'monthNames' => array_values($wp_locale->month_abbrev),
					'tickLength' => 1,
					'minTickSize' => array(1, $this->chartGroupBy),
					'font' => $this->arrayToObject(array('color' => '#aaa')),
				))
			),
			'yaxes' => array(
				$this->arrayToObject(array(
					'min' => 0,
					'minTickSize' => 1,
					'tickDecimals' => 0,
					'color' => '#ecf0f1',
					'font' => $this->arrayToObject(array('color' => '#aaa'))
				)),
				$this->arrayToObject(array(
					'position' => 'right',
					'min' => 0,
					'tickDecimals' => 2,
					'alignTicksWithAxis' => 1,
					'autoscaleMargin' => 0,
					'color' => 'transparent',
					'font' => $this->arrayToObject(array('color' => '#aaa'))
				))
			),
		));
		if ($this->chartGroupBy == 'hour') {
			$data['options']->xaxes[0]->min = 0;
			$data['options']->xaxes[0]->max = 24 * 60 * 60 * 1000;
		}

		return $data;
	}

	/**
	 *
	 */
	private function getChartColours()
	{
		$this->chartColours = $this->wp->applyFilters('jigoshop/admin/reports/discount_summary/chart_colours', array(

			'discount_amount' => '#3498db',
			'coupon_count' => '#d4d9dc',
		));
	}

	/**
	 * @return array
	 */
	private function getUsedCoupons()
	{
		$data = $this->getReportData();
		$usedCoupons = array();

		foreach ($data->usedCoupons as $coupons) {
			foreach ($coupons->coupons as $coupon) {
				if (!empty($coupon)) {
					if (!isset($usedCoupons[$coupon['code']])) {
						$usedCoupons[$coupon['code']] = $coupon;
						$usedCoupons[$coupon['code']]['usage'] = 0;
						$usedCoupons[$coupon['code']]['amount'] = 0;
					}
					$usedCoupons[$coupon['code']]['usage'] += $coupons->usage[$coupon['code']];
					$usedCoupons[$coupon['code']]['amount'] += $coupon['amount'];
				}
			}
		}

		return $usedCoupons;
	}

	/**
	 * @param $orders
	 *
	 * @return mixed
	 */
	private function parseReportData($orders)
	{
		foreach ($orders as $order) {
			$discounts = $this->calculateDiscounts($order);
			$order->coupons = $discounts;
		}

		return $orders;
	}

	/**
	 * @param $order
	 *
	 * @return array
	 */
	private function calculateDiscounts($order)
	{
		$order->coupons = unserialize($order->coupons);
		$flatDiscount = array();
		if (sizeof($order->coupons) > 1) {
			$percentageDiscount = array();
			foreach ($order->coupons as $coupon) {

				$flatDiscount[$coupon['code']] = array(
					'amount' => '',
					'usage' => 0
				);
				$percentageDiscount[$coupon['code']] = array(
					'amount' => '',
					'usage' => 0
				);
				if ($coupon['type'] == 'percent') {
					$percentageDiscount[$coupon['code']]['amount'] = $coupon['amount'];
					$percentageDiscount[$coupon['code']]['usage'] += $coupon['individual_use'] ? 1 : $coupon['usage'];
				} else if ($coupon['type'] == 'percent_product') {
					$includedProducts = array();
					foreach ($coupon['include_products'] as $productId) {
						$parent = $this->getParent($productId);
						if ($parent) {
							$includedProducts[] = $parent;
						} else {
							$includedProducts[] = $productId;
						}
					}
					$orderProducts = $this->getOrderProducts($order->order_id);
					foreach ($orderProducts as $orderProduct) {
						if (in_array($orderProduct->product_id, $includedProducts)) {
							$flatDiscount[$coupon['code']]['amount'] += $orderProduct->cost * $coupon['amount'] / 100;
						}
						$flatDiscount[$coupon['code']]['usage'] += $coupon['individual_use'] ? 1 : $coupon['usage'];
					}
				} else {
					$flatDiscount[$coupon['code']] = $coupon['amount'];
					$flatDiscount[$coupon['code']]['usage'] += $coupon['individual_use'] ? 1 : $coupon['usage'];
				}
			}

			$totalDiscountByPercentageCoupons = $order->discount - array_sum(array_map(function ($coupon){
					return $coupon['amount'];
				}, $flatDiscount));
			foreach ($percentageDiscount as $code => $data) {
				$flatDiscount[$code]['amount'] = $data['amount'] * $totalDiscountByPercentageCoupons / array_sum(array_map(function ($coupon){
						return $coupon['amount'];
					}, $percentageDiscount));
			}
		} else {
			$flatDiscount[$order->coupons[0]['code']] = array(
				'amount' => '',
				'usage' => 0
			);
			$flatDiscount[$order->coupons[0]['code']]['amount'] = $order->discount;
			$flatDiscount[$order->coupons[0]['code']]['usage'] += $order->coupons[0]['individual_use'] ? 1 : $order->coupons[0]['usage'] == '' ? 1 : $order->coupons[0]['usage'];
		}

		return $flatDiscount;
	}

	/**
	 * @param $orderId
	 *
	 * @return mixed
	 */
	private function getOrderProducts($orderId)
	{
		$wpdb = $this->wp->getWPDB();

		return $wpdb->get_results($wpdb->prepare('SELECT * FROM '.$wpdb->prefix.'jigoshop_order_item WHERE order_id = %s', $orderId));
	}

	/**
	 * @param $productId
	 *
	 * @return null|string
	 */
	private function getParent($productId)
	{
		$wpdb = $this->wp->getWPDB();

		return $wpdb->get_var($wpdb->prepare("SELECT post_parent FROM {$wpdb->posts} WHERE ID = %d", $productId));
	}
}