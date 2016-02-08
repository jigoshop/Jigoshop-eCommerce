<?php

namespace Jigoshop\Admin\Reports\Chart;

use Jigoshop\Admin\Reports;
use Jigoshop\Admin\Reports\Chart;
use Jigoshop\Core\Options;
use Jigoshop\Helper\Scripts;
use Jigoshop\Helper\Render;
use WPAL\Wordpress;

class CustomersVsGuests extends Chart
{
	public $chartColours = array();
	public $reportData;

	public function __construct(Wordpress $wp, Options $options, $currentRange)
	{
		parent::__construct($wp, $options, $currentRange);

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
			Scripts::localize('jigoshop.reports.chart', 'chart_data', $this->getMainChart());
		});
	}

	public function getChartLegend()
	{
		$legend = array();
		$mapTotalOrder = function ($order){
			return $order->total_orders;
		};
		$customerOrderCount = array_sum(array_map($mapTotalOrder, $this->reportData->customerOrders));
		$guestOrderCount = array_sum(array_map($mapTotalOrder, $this->reportData->guestOrders));

		$legend[] = array(
			'title' => sprintf(__('%s customer orders in this period', 'jigoshop'), '<strong>'.$customerOrderCount.'</strong>'),
			'color' => $this->chartColours['signups'],
			'highlight_series' => 0
		);
		$legend[] = array(
			'title' => sprintf(__('%s guest orders in this period', 'jigoshop'), '<strong>'.$guestOrderCount.'</strong>'),
			'color' => $this->chartColours['signups'],
			'highlight_series' => 1
		);
		$legend[] = array(
			'title' => sprintf(__('%s signups in this period', 'jigoshop'), '<strong>'.sizeof($this->reportData->customers).'</strong>'),
			'color' => $this->chartColours['signups'],
			'highlight_series' => 2
		);

		return $legend;
	}

	public function getChartWidgets()
	{
		$widgets = array();

		$widgets[] = new Chart\Widget\CustomRange();

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

	public function queryReportData()
	{
		$this->reportData = new \stdClass();
		$wpdb = $this->wp->getWPDB();

		$query = $this->prepareQuery(array(
			'select' => array(
				'customer' => array(
					array(
						'field' => 'meta_value',
						'function' => '',
						'name' => 'customer_user'
					)
				),
				'posts' => array(
					array(
						'field' => 'ID',
						'function' => 'COUNT',
						'name' => 'total_orders',
						'distinct' => true
					),
					array(
						'field' => 'post_date',
						'function' => '',
						'name' => 'post_date',
					)
				),
			),
			'from' => array(
				'posts' => $wpdb->posts,
			),
			'join' => array(
				'customer' => array(
					'table' => $wpdb->postmeta,
					'on' => array(
						array(
							'key' => 'post_id',
							'value' => 'posts.ID',
							'compare' => '=',
						)
					),
				),
			),
			'where' => array(
				array(
					'key' => 'customer.meta_value',
					'value' => '0',
					'compare' => '>'
				)
			),
			'group_by' => $this->groupByQuery,
			'order_by' => 'post_date ASC',
			'filter_range' => true
		));

		$this->reportData->customerOrders = $this->getOrderReportData($query);

		$query = $this->prepareQuery(array(
			'select' => array(
				'customer' => array(
					array(
						'field' => 'meta_value',
						'function' => '',
						'name' => 'customer_user'
					)
				),
				'posts' => array(
					array(
						'field' => 'ID',
						'function' => 'COUNT',
						'name' => 'total_orders',
						'distinct' => true
					),
					array(
						'field' => 'post_date',
						'function' => '',
						'name' => 'post_date',
					)
				),
			),
			'from' => array(
				'posts' => $wpdb->posts,
			),
			'join' => array(
				'customer' => array(
					'table' => $wpdb->postmeta,
					'on' => array(
						array(
							'key' => 'post_id',
							'value' => 'posts.ID',
							'compare' => '=',
						)
					),
				),
			),
			'where' => array(
				array(
					'key' => 'customer.meta_value',
					'value' => '0',
					'compare' => '='
				)
			),
			'group_by' => $this->groupByQuery,
			'order_by' => 'post_date ASC',
			'filter_range' => true
		));

		$this->reportData->guestOrders = $this->getOrderReportData($query);

		$adminUsers = new \WP_User_Query(array(
			'role' => 'administrator',
			'fields' => 'ID'
		));

		$managerUsers = new \WP_User_Query(array(
			'role' => 'shop_manager',
			'fields' => 'ID'
		));

		$usersQuery = new \WP_User_Query(array(
			'fields' => array('user_registered'),
			'exclude' => array_merge($adminUsers->get_results(), $managerUsers->get_results())
		));

		$this->reportData->customers = $usersQuery->get_results();
		foreach ($this->reportData->customers as $key => $customer) {
			if (strtotime($customer->user_registered) < $this->range['start'] || strtotime($customer->user_registered) > $this->range['end']) {
				unset($this->reportData->customers[$key]);
			}
		}
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

		Render::output('admin/reports/chart', array(
			/** TODO This is ugly... */
			'current_tab' => Reports\CustomersTab::SLUG,
			'current_type' => 'customers_vs_guests',
			'ranges' => $ranges,
			'url' => remove_query_arg(array('start_date', 'end_date')),
			'current_range' => $this->currentRange,
			'legends' => $this->getChartLegend(),
			'widgets' => $this->getChartWidgets(),
			'group_by' => $this->chartGroupBy
		));
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
		$signups = $this->prepareChartData($this->reportData->customers, 'user_registered', '', $this->chartInterval, $this->range['start'], $this->chartGroupBy);
		$customerOrders = $this->prepareChartData($this->reportData->customerOrders, 'post_date', 'total_orders', $this->chartInterval, $this->range['start'], $this->chartGroupBy);
		$guestOrders = $this->prepareChartData($this->reportData->guestOrders, 'post_date', 'total_orders', $this->chartInterval, $this->range['start'], $this->chartGroupBy);

		$data = array();
		$data['series'] = array();
		$data['series'][] = $this->arrayToObject(array(
			'label' => __('Customer Orders', 'jigoshop'),
			'data' => array_values($customerOrders),
			'color' => $this->chartColours['customers'],
			'bars' => $this->arrayToObject(array(
				'fillColor' => $this->chartColours['customers'],
				'fill' => true,
				'show' => true,
				'lineWidth' => 0,
				'align' => 'right',
				'barWidth' => $this->barwidth * 0.4,
			)),
			'shadowSize' => 0,
			'enable_tooltip' => true,
			'append_tooltip' => sprintf(' %s', __('customer orders', 'jigoshop')),
			'hoverable' => false
		));
		$data['series'][] = $this->arrayToObject(array(
			'label' => __('Guest Orders', 'jigoshop'),
			'data' => array_values($guestOrders),
			'color' => $this->chartColours['guests'],
			'bars' => $this->arrayToObject(array(
				'fillColor' => $this->chartColours['guests'],
				'fill' => true,
				'show' => true,
				'lineWidth' => 0,
				'align' => 'left',
				'barWidth' => $this->barwidth * 0.4,
			)),
			'shadowSize' => 0,
			'enable_tooltip' => true,
			'append_tooltip' => sprintf(' %s', __('guest orders', 'jigoshop')),
			'hoverable' => false
		));
		$data['series'][] = $this->arrayToObject(array(
			'label' => __('Signups', 'jigoshop'),
			'data' => array_values($signups),
			'color' => $this->chartColours['signups'],
			'points' => $this->arrayToObject(array(
				'show' => true,
				'radius' => 5,
				'lineWidth' => 3,
				'fillColor' => '#fff',
				'fill' => true
			)),
			'lines' => $this->arrayToObject(array(
				'show' => true,
				'lineWidth' => 4,
				'fill' => false
			)),
			'shadowSize' => 0,
			'enable_tooltip' => true,
			'append_tooltip' => sprintf(' %s', __('new users', 'jigoshop')),
			'stack' => false
		));

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
					'position' => 'bottom',
					'tickColor' => 'transparent',
					'mode' => 'time',
					'timeformat' => $this->chartGroupBy == 'hour' ? '%H' : $this->chartGroupBy == 'day' ? '%d %b' : '%b',
					'monthNames' => array_values($wp_locale->month_abbrev),
					'tickLength' => 1,
					'minTickSize' => array(1, $this->chartGroupBy),
					'tickSize' => array(1, $this->chartGroupBy),
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
				))
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
		return $this->chartColours = array(
			'signups' => '#3498db',
			'customers' => '#1abc9c',
			'guests' => '#8fdece'
		);
	}
}