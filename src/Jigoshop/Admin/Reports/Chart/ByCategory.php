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

class ByCategory extends Chart
{
	public $chartColours = array();
	public $showCategories = array();
	private $itemSales = array();
	private $itemSalesAndTimes = array();
	private $reportData;

	/**
	 * @param Wordpress $wp
	 * @param Options   $options
	 * @param string    $currentRange
	 */
	public function __construct(Wordpress $wp, Options $options, $currentRange)
	{
		parent::__construct($wp, $options, $currentRange);
		if (isset($_GET['show_categories'])) {
			if(in_array(-1, $_GET['show_categories'])) {
				$allCategories = get_terms('product_category', array('orderby' => 'name', 'hide_empty' => false));
				$this->showCategories = array_map(function($category){
					return $category->term_id;
				}, $allCategories);
			} else {
				$this->showCategories = is_array($_GET['show_categories']) ? array_map('absint',
					$_GET['show_categories']) : array(absint($_GET['show_categories']));
			}
		} else {
			$this->showCategories = $this->getLastCategoryID();
		}

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
			Styles::add('jigoshop.vendors.select2', \Jigoshop::getUrl().'/assets/css/vendors/select2.css', array('jigoshop.admin.reports'));
			Scripts::add('jigoshop.vendors.select2', \Jigoshop::getUrl().'/assets/js/vendors/select2.js', array('jigoshop.admin.reports'), array('in_footer' => true));
			Scripts::localize('jigoshop.reports.chart', 'chart_data', $this->getMainChart());
		});
	}

	/**
	 * Get the legend for the main chart sidebar
	 *
	 * @return array
	 */
	public function getChartLegend()
	{
		if (!$this->showCategories) {
			return array();
		}

		$legend = array();
		$index = 0;

		foreach ($this->showCategories as $category) {
			$category = get_term($category, 'product_category');
			$total = 0;
			$product_ids = $this->getProductsInCategory($category->term_id);

			foreach ($product_ids as $id) {
				if (isset($this->reportData->itemSales[$id])) {
					$total += $this->reportData->itemSales[$id];
				}
			}

			$legend[] = array(
				'title' => sprintf(__('%s sales in %s', 'jigoshop'), '<strong>'.Product::formatPrice($total).'</strong>', $category->name),
				'color' => $this->chartColours[$index % sizeof($this->chartColours)],
				'highlight_series' => $index
			);

			$index++;
		}

		return $legend;
	}

	public function getReportData()
	{
		if (empty($this->reportData)) {
			$this->queryReportData();
		}

		return $this->reportData;
	}

	private function queryReportData()
	{
		$this->reportData = new \stdClass();
		$wpdb = $this->wp->getWPDB();
		// Get item sales data
		if ($this->showCategories) {
			$query = $this->prepareQuery(array(
				'select' => array(
					'order_item' => array(
						array(
							'field' => 'cost',
							'function' => '',
							'name' => 'price',
						),
						array(
							'field' => 'product_id',
							'function' => '',
							'name' => 'id',
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
					'order_item' => $wpdb->prefix.'jigoshop_order_item',
				),
				'join' => array(
					'posts' => array(
						'table' => $wpdb->posts,
						'on' => array(
							array(
								'key' => 'ID',
								'value' => 'order_item.order_id',
								'compare' => '=',
							)
						),
					),
				),
				'filter_range' => true,
			));
			$orders = $this->getOrderReportData($query);

			$this->reportData->itemSales = array();
			$this->reportData->itemSalesAndTimes = array();

			if (is_array($orders)) {
				foreach ($orders as $orderItem) {
					switch ($this->chartGroupBy) {
						case 'hour' :
							$time = (date('H', strtotime($orderItem->post_date)) * 3600).'000';
							break;
						case 'day' :
							$time = strtotime(date('Ymd', strtotime($orderItem->post_date))) * 1000;
							break;
						case 'month' :
						default :
							$time = strtotime(date('Ym', strtotime($orderItem->post_date)).'01') * 1000;
							break;
					}

					$this->reportData->itemSalesAndTimes[$time][$orderItem->id] = isset($this->reportData->itemSalesAndTimes[$time][$orderItem->id]) ? $this->reportData->itemSalesAndTimes[$time][$orderItem->id] + $orderItem->price : $orderItem->price;
					$this->reportData->itemSales[$orderItem->id] = isset($this->reportData->itemSales[$orderItem->id]) ? $this->reportData->itemSales[$orderItem->id] + $orderItem->price : $orderItem->price;
				}
			}
		}
	}

	/**
	 * Get all product ids in a category (and its children)
	 *
	 * @param  int $categoryId
	 *
	 * @return array
	 */
	public function getProductsInCategory($categoryId)
	{
		$termIds = get_term_children($categoryId, 'product_category');
		$termIds[] = $categoryId;
		$productIds = get_objects_in_term($termIds, 'product_category');

		return array_unique($this->wp->applyFilters('jigoshop\admin\reports\by_category\products_in_category', $productIds, $categoryId));
	}

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

		$this->calculateCurrentRange();

		Render::output('admin/reports/chart', array(
			/** TODO This is ugly... */
			'current_tab' => Reports\SalesTab::SLUG,
			'current_type' => 'by_category',
			'ranges' => $ranges,
			'url' => remove_query_arg(array('start_date', 'end_date')),
			'current_range' => $this->currentRange,
			'legends' => $this->getChartLegend(),
			'widgets' => $this->getChartWidgets(),
			'export' => $this->getExportButton(),
			'group_by' => $this->chartGroupBy
		));
	}

	public function getChartWidgets()
	{
		$widgets = array();
		$categories = get_terms('product_category', array('orderby' => 'name', 'hide_empty' => false));
		$allCategories = array();
		foreach ($categories as $category) {
			$allCategories[$category->term_id] = $category->name;
		}
		$allCategories[-1] = __('All categories', 'jigoshop');
		
		$widgets[] = new Chart\Widget\CustomRange();
		$widgets[] = new Chart\Widget\SelectCategories($this->showCategories, $allCategories);

		return $this->wp->applyFilters('jigoshop\admin\reports\by_category\widgets', $widgets);
	}

	public function getExportButton()
	{
		return array(
			'download' => 'report-'.esc_attr($this->currentRange).'-'.date_i18n('Y-m-d', current_time('timestamp')).'.csv',
			'xaxes' => __('Date', 'jigoshop'),
			'groupby' => $this->chartGroupBy,
		);
	}

	public function getMainChart()
	{
		global $wp_locale;

		$chartData = array();
		$index = 0;
		foreach ($this->showCategories as $category) {
			$category = get_term($category, 'product_category');
			$productIds = $this->getProductsInCategory($category->term_id);;
			$categoryTotal = 0;
			$categoryChartData = array();
			for ($i = 0; $i <= $this->chartInterval; $i++) {
				$intervalTotal = 0;
				switch ($this->chartGroupBy) {
					case 'hour' :
						$time = strtotime(date('YmdHi', strtotime($this->range['start']))) + $i * 3600000;
						break;
					case 'day' :
						$time = strtotime(date('Ymd', strtotime("+{$i} DAY", $this->range['start']))) * 1000;
						break;
					case 'month' :
					default :
						$time = strtotime(date('Ym', strtotime("+{$i} MONTH", $this->range['start'])).'01') * 1000;
						break;
				}
				foreach ($productIds as $id) {
					if (isset($this->reportData->itemSalesAndTimes[$time][$id])) {
						$intervalTotal += $this->reportData->itemSalesAndTimes[$time][$id];
						$categoryTotal += $this->reportData->itemSalesAndTimes[$time][$id];
					}
				}
				$categoryChartData[] = array($time, $intervalTotal);
			}
			$chartData[$category->term_id]['category'] = $category->name;
			$chartData[$category->term_id]['data'] = $categoryChartData;
			$index++;
		}

		$index = 0;
		$data = array();
		$data['series'] = array();
		foreach ($chartData as $singleData) {
			$width = $this->barwidth / sizeof($chartData);
			$offset = ($width * $index);
			foreach ($singleData['data'] as $key => $seriesData) {
				$singleData['data'][$key][0] = $seriesData[0] + $offset;
			}
			$data['series'][] = $this->arrayToObject(array(
				'label' => esc_js($singleData['category']),
				'data' => $singleData['data'],
				'color' => $this->chartColours[$index % sizeof($this->chartColours)],
				'bars' => $this->arrayToObject(array(
					'fillColor' => $this->chartColours[$index % sizeof($this->chartColours)],
					'fill' => true,
					'show' => true,
					'lineWidth' => 1,
					'align' => 'center',
					'barWidth' => $width * 0.8,
					'stack' => false
				)),
				'append_tooltip' => Currency::symbol(),
				'enable_tooltip' => true
			));
			$index++;
		}
		$data['options'] = $this->arrayToObject(array(
			'legend' => $this->arrayToObject(array('show' => false)),
			'grid' => $this->arrayToObject(array(
				'color' => '#aaa',
				'borderColor' => 'transparent',
				'borderWidth' => 0,
				'hoverable' => true
			)),
			'xaxes' => array(
				$this->arrayToObject(array(
					'color' => '#aaa',
					'reserveSpace' => false,
					'position' => 'bottom',
					'tickColor' => 'transparent',
					'mode' => 'time',
					'timeformat' => $this->chartGroupBy == 'hour' ? '%H' : $this->chartGroupBy == 'day' ? '%d %b' : '%b',
					'monthNames' => array_values($wp_locale->month_abbrev),
					'tickLength' => 1,
					'minTickSize' => array(1, $this->chartGroupBy),
					'tickSize' => array(1, $this->chartGroupBy),
					'font' => $this->arrayToObject(array('color' => '#aaa')),
				)),
			),
			'yaxes' => array(
				$this->arrayToObject(array(
					'min' => 0,
					'tickDecimals' => 2,
					'color' => '#d4d9dc',
					'font' => $this->arrayToObject(array('color' => '#aaa'))
				)),
			),
		));
		if ($this->chartGroupBy == 'hour') {
			$data['options']->xaxes[0]->min = 0;
			$data['options']->xaxes[0]->max = 24 * 60 * 60 * 1000;
		}

		return $data;
	}

	private function getChartColours()
	{
		$this->chartColours = $this->wp->applyFilters('jigoshop\admin\reports\by_category\chart_colours', array(
			'#3498db',
			'#2ecc71',
			'#f1c40f',
			'#e67e22',
			'#8e44ad',
			'#d35400',
			'#5bc0de',
			'#EAA6EA',
			'#FFC8C8',
			'#AAAAFF',
			'#B6BA18',

		));
	}

	private function getLastCategoryID()
	{
		$wpdb = $this->wp->getWPDB();
		$productID = $wpdb->get_var($wpdb->prepare("SELECT term_id FROM $wpdb->term_taxonomy WHERE taxonomy = %s ORDER BY term_taxonomy_id DESC LIMIT 1", 'product_category'));

		return $productID ? array($productID) : array();
	}
}