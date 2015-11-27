<?php

namespace Jigoshop\Admin\Reports;

use Jigoshop\Admin\Reports;
use Jigoshop\Core\Options;
use Jigoshop\Helper\Currency;
use Jigoshop\Helper\Scripts;
use WPAL\Wordpress;

/**
 * Class Chart
 *
 * @package Jigoshop\Admin\Reports
 * @author  Krzysztof Kasowski
 *
 */
abstract class Chart
{
	/** @var Wordpress  */
	protected $wp;
	/** @var Options  */
	protected $options;
	/** @var array  */
	protected $charColors = array();
	/** @var array  */
	protected $range = array();
	/** @var array  */
	protected $orderStatus = array();
	/** @var  string */
	protected $currentRange;
	/** @var  string */
	protected $chartGroupBy;
	/** @var  string */
	protected $groupByQuery;
	/** @var  int */
	protected $chartInterval;
	/** @var  int */
	protected $barwidth;

	public function __construct(Wordpress $wp, Options $options, $currentRange)
	{
		$this->wp = $wp;
		$this->options = $options;
		$this->currentRange = $currentRange;
		$this->orderStatus = isset($_GET['order_status']) && !empty($_GET['order_status']) ? $_GET['order_status'] : array('jigoshop-completed', 'jigoshop-processing');
		$wp->addAction('admin_enqueue_scripts', function () use ($wp){
			// Weed out all admin pages except the Jigoshop Settings page hits
			if (!in_array($wp->getPageNow(), array('admin.php', 'options.php'))) {
				return;
			}

			$screen = $wp->getCurrentScreen();
			if ($screen->base != 'jigoshop_page_'.Reports::NAME) {
				return;
			}
			Scripts::add('jigoshop.flot', JIGOSHOP_URL.'/assets/js/flot/jquery.flot.min.js', array('jquery'));
			Scripts::add('jigoshop.flot.time', JIGOSHOP_URL.'/assets/js/flot/jquery.flot.time.min.js', array(
					'jquery',
					'jigoshop.flot'
			));
			Scripts::add('jigoshop.flot.pie', JIGOSHOP_URL.'/assets/js/flot/jquery.flot.pie.min.js', array(
					'jquery',
					'jigoshop.flot'
			));
			Scripts::add('jigoshop.reports.chart', JIGOSHOP_URL.'/assets/js/admin/reports/chart.js', array(
					'jquery',
					'jigoshop.flot'
			));
		});
	}

	/**
	 * Output the report
	 */
	abstract public function display();

	/**
	 * Get the main chart
	 *
	 * @return array
	 */
	abstract public function getMainChart();

	/**
	 * Get the legend for the main chart sidebar
	 *
	 * @return array
	 */
    abstract public function getChartLegend();

	/**
	 * [get_chart_widgets description]
	 *
	 * @return array
	 */
	abstract public function getChartWidgets();

	/**
	 * Get an export link if needed
	 */
	abstract public function getExportButton();

	/**
	 * Get the current range and calculate the start and end dates
	 *
	 */
	protected function calculateCurrentRange()
	{
		switch ($this->currentRange) {
			case 'custom' :
				$this->range['start'] = strtotime(sanitize_text_field($_GET['start_date']));
				$this->range['end'] = strtotime('midnight', strtotime(sanitize_text_field($_GET['end_date'])));

				if (!$this->range['end']) {
					$this->range['end'] = current_time('timestamp');
				}

				$interval = 0;
				$min_date = $this->range['start'];

				while (($min_date = strtotime("+1 MONTH", $min_date)) <= $this->range['end']) {
					$interval++;
				}

				// 3 months max for day view
				if ($interval > 3) {
					$this->chartGroupBy = 'month';
				} else {
					$interval = 0;
					$min_date = $this->range['start'];
					while (($min_date = strtotime("+1 day", $min_date)) <= $this->range['end']) {
						$interval++;
					}
					if($interval > 0){
						$this->chartGroupBy = 'day';
					} else {
						$this->chartGroupBy = 'hour';
					}
				}
				break;
			case 'all' :
				$this->range['start'] = strtotime(date('Y-m-d', strtotime($this->getFirstOrderDate())));
				$this->range['end'] = strtotime('midnight', current_time('timestamp'));
				$this->chartGroupBy = 'month';
				break;
			case 'year' :
				$this->range['start'] = strtotime(date('Y-01-01', current_time('timestamp')));
				$this->range['end'] = strtotime('midnight', current_time('timestamp'));
				$this->chartGroupBy = 'month';
				break;
			case 'last_month' :
				$first_day_current_month = strtotime(date('Y-m-01', current_time('timestamp')));
				$this->range['start'] = strtotime(date('Y-m-01', strtotime('-1 DAY', $first_day_current_month)));
				$this->range['end'] = strtotime(date('Y-m-t', strtotime('-1 DAY', $first_day_current_month)));
				$this->chartGroupBy = 'day';
				break;
			case 'month' :
				$this->range['start'] = strtotime(date('Y-m-01', current_time('timestamp')));
				$this->range['end'] = strtotime('midnight', current_time('timestamp'));
				$this->chartGroupBy = 'day';
				break;
			case '30day' :
				$this->range['start'] = strtotime('-29 days', current_time('timestamp'));
				$this->range['end'] = strtotime('midnight', current_time('timestamp'));
				$this->chartGroupBy = 'day';
				break;
			case '7day' :
				$this->range['start'] = strtotime('-6 days', current_time('timestamp'));
				$this->range['end'] = strtotime('midnight', current_time('timestamp'));
				$this->chartGroupBy = 'day';
				break;
			case 'today' :
				$this->range['start'] = strtotime('midnight', current_time('timestamp'));
				$this->range['end'] = strtotime('+1 hour', current_time('timestamp'));
				$this->chartGroupBy = 'hour';
				break;
		}

		// Group by
		switch ($this->chartGroupBy) {
			case 'hour' :
				$this->groupByQuery = 'YEAR(posts.post_date), MONTH(posts.post_date), DAY(posts.post_date), HOUR(posts.post_date)';
				$this->chartInterval = ceil(max(0, ($this->range['end'] - $this->range['start']) / (60 * 60)));
				$this->barwidth = 60 * 60 * 1000;
				break;
			case 'day' :
				$this->groupByQuery = 'YEAR(posts.post_date), MONTH(posts.post_date), DAY(posts.post_date)';
				$this->chartInterval = ceil(max(0, ($this->range['end'] - $this->range['start']) / (60 * 60 * 24)));
				$this->barwidth = 60 * 60 * 24 * 1000;
				break;
			case 'month' :
				$this->groupByQuery = 'YEAR(posts.post_date), MONTH(posts.post_date)';
				$this->chartInterval = 0;
				$min_date = $this->range['start'];

				while (($min_date = strtotime("+1 MONTH", $min_date)) <= $this->range['end']) {
					$this->chartInterval++;
				}

				$this->barwidth = 60 * 60 * 24 * 7 * 4 * 1000;
				break;
		}
	}

	/**
	 * Put data with post_date's into an array of times
	 *
	 * @param  array $data array of your data
	 * @param  string $dateKey key for the 'date' field. e.g. 'post_date'
	 * @param  string $dataKey key for the data you are charting
	 * @param  int $interval
	 * @param  string $startDate
	 * @param  string $groupBy
	 * @return array
	 */
	protected function prepareChartData($data, $dateKey, $dataKey, $interval, $startDate, $groupBy)
	{
		$preparedData = array();

		// Ensure all days (or months) have values first in this range
		for ($i = 0; $i <= $interval; $i++) {
			switch ($groupBy) {
				case 'hour' :
					$time = strtotime(date('YmdHi', strtotime($startDate)))+$i*3600000;
					break;
				case 'day' :
					$time = strtotime(date('Ymd', strtotime("+{$i} DAY", $startDate))).'000';
					break;
				case 'month' :
				default :
					$time = strtotime(date('Ym', strtotime("+{$i} MONTH", $startDate)).'01').'000';
					break;
			}

			if (!isset($preparedData[$time])) {
				$preparedData[$time] = array(esc_js($time), 0);
			}
		}

		foreach ($data as $d) {
			switch ($groupBy) {
				case 'hour' :
					$time = (date('H', strtotime($d->$dateKey))*3600).'000';
					break;
				case 'day' :
					$time = strtotime(date('Ymd', strtotime($d->$dateKey))).'000';
					break;
				case 'month' :
				default :
					$time = strtotime(date('Ym', strtotime($d->$dateKey)).'01').'000';
					break;
			}

			if (!isset($preparedData[$time])) {
				continue;
			}

			if ($dataKey) {
				$preparedData[$time][1] += $d->$dataKey;
			} else {
				$preparedData[$time][1]++;
			}
		}

		return $preparedData;
	}

	/**
	 * Return currency tooltip JS based on jigoshop currency position settings.
	 *
	 * @return string
	 */
	protected function getCurrencyTooltip()
	{
		$pattern = $this->options->get('general.currency_position');
		switch ($pattern) {
			//right
			case '%3$s%1$s':
			case '%3$s%2$s':
			case '%2$s%3$s%1$s':
				$currencyTooltip = 'append_tooltip: "'.Currency::symbol().'"';
				break;
			//right space
			case '%3$s %1$s':
			case '%3$s %2$s':
			case '%2$s %3$s %1$s':
				$currencyTooltip = 'append_tooltip: "&nbsp;'.Currency::symbol().'"';
				break;
			//left
			case '%1$s%3$s':
			case '2$s%3$s':
			case '%1$s%3$s%2$s':
				$currencyTooltip = 'prepend_tooltip: "'.Currency::symbol().'"';
				break;
			//left space
			case '%1$s 0%2$s00':
			case '2$s %3$s':
			case '%1$s %3$s %2$s':
			default:
				$currencyTooltip = 'prepend_tooltip: "'.Currency::symbol().'&nbsp;"';
				break;
		}

		return $currencyTooltip;
	}

	protected function getFirstOrderDate()
	{
		$args = array(
			'posts_per_page'   => 1,
			'offset'           => 0,
			'orderby'          => 'post_date',
			'order'            => 'ASC',
			'post_type'        => 'shop_order',
		);
		$postsArray = get_posts( $args );

		return $postsArray[0]->post_date;
	}

	protected function filterItem($item, $value)
	{
		if (isset($value['where'])) {
			switch($value['where']['type']) {
				case 'item_id':
					$item = array_filter($item, function($product) use ($value){
						$result = false;
						foreach ($value['where']['keys'] as $key) {
							$result |= in_array($product[$key], $value['where']['value']);
						}

						return $result;
					});
					break;
				case 'comparison':
					$item = array_filter($item, function($product) use ($value){
						switch ($value['where']['operator']) {
							case '<>':
							case '!=':
								return $product[$value['where']['key']] != $value['where']['value'];
							case '=':
								return $product[$value['where']['key']] == $value['where']['value'];
							case '<':
								return $product[$value['where']['key']] < $value['where']['value'];
							case '>':
								return $product[$value['where']['key']] > $value['where']['value'];
							case '<=':
								return $product[$value['where']['key']] <= $value['where']['value'];
							case '>=':
								return $product[$value['where']['key']] >= $value['where']['value'];
							case 'in':
								return in_array($product[$value['where']['key']], $value['where']['value']);
							case 'intersection':
								$intersection = array_intersect($product[$value['where']['key']], $value['where']['value']);
								return !empty($intersection);
						}

						return false;
					});
					break;
				case 'object_comparison':
					switch ($value['where']['operator']) {
						case '<>':
						case '!=':
							if ($item[$value['where']['key']] != $value['where']['value']) {
								return $item;
							}
						case '=':
							if ($item[$value['where']['key']] == $value['where']['value']) {
								return $item;
							}
						case '<':
							if ($item[$value['where']['key']] < $value['where']['value']) {
								return $item;
							}
						case '>':
							if ($item[$value['where']['key']] > $value['where']['value']) {
								return $item;
							}
						case '<=':
							if ($item[$value['where']['key']] <= $value['where']['value']) {
								return $item;
							}
						case '>=':
							if ($item[$value['where']['key']] >= $value['where']['value']) {
								return $item;
							}
						case 'in':
							if (in_array($item[$value['where']['key']], $value['where']['value'])) {
								return $item;
							}
						case 'intersection':
							$source = $item[$value['where']['key']];
							if(is_array($source)) {
								if (isset($value['where']['map'])) {
									$source = array_map($value['where']['map'], $source);
								}

								$intersection = array_intersect($source, $value['where']['value']);
								if (!empty($intersection)) {
									return $item;
								};
							}
					}

					return false;
			}
		}

		return $item;
	}

	/**
	 * Get report totals such as order totals and discount amounts.
	 * Data example:
	 * '_order_total' => array(
	 *     'type'     => 'meta',
	 *     'function' => 'SUM',
	 *     'name'     => 'total_sales'
	 * )
	 *
	 * @param  array $args
	 * @return array|string depending on query_type
	 */
	public function getOrderReportData($args = array())
	{
		$wpdb = $this->wp->getWPDB();

		$defaultArgs = array(
			'data' => array(),
			'where' => array(),
			'where_meta' => array(),
			'query_type' => 'get_row',
			'group_by' => '',
			'order_by' => '',
			'limit' => '',
			'filter_range' => false,
			'nocache' => false,
			'debug' => false,
			'order_types' => array('shop_order'),
			'order_status' => $this->orderStatus,
			'parent_order_status' => false,
		);
		$args = $this->wp->applyFilters('jigoshop/admin/reports/chart/report_data_args', $args);
		$args = wp_parse_args($args, $defaultArgs);

		if (empty($args['data'])) {
			return '';
		}

		$orderStatus = $this->wp->applyFilters('jigoshop/admin/reports/order_statuses', $args['order_status']);
		$query = array();
		$select = array();

		foreach ($args['data'] as $key => $value) {
			$distinct = '';

			if (isset($value['distinct'])) {
				$distinct = 'DISTINCT';
			}

			if ($value['type'] == 'meta') {
				$getKey = "meta_{$key}.meta_value";
			} elseif ($value['type'] == 'post_data') {
				$getKey = "posts.{$key}";
			} else {
				continue;
			}

			if (isset($value['function'])) {
				$get = "{$value['function']}({$distinct} {$getKey})";
			} else {
				$get = "{$distinct} {$getKey}";
			}

			$select[] = "{$get} as {$value['name']}";
		}

		$query['select'] = "SELECT ".implode(',', $select);
		$query['from'] = "FROM {$wpdb->posts} AS posts";

		// Joins
		$joins = array();

		foreach ($args['data'] as $key => $value) {
			if ($value['type'] == 'meta') {
				$joins["meta_{$key}"] = "LEFT JOIN {$wpdb->postmeta} AS meta_{$key} ON posts.ID = meta_{$key}.post_id";
			}
		}

		foreach ($args['where_meta'] as $value) {
			if (!is_array($value)) {
				continue;
			}

			$key = is_array($value['meta_key']) ? $value['meta_key'][0].'_array' : $value['meta_key'];
			$joins["meta_{$key}"] = "LEFT JOIN {$wpdb->postmeta} AS meta_{$key} ON posts.ID = meta_{$key}.post_id";
		}

		if (!empty($args['parent_order_status'])) {
			$joins["parent"] = "LEFT JOIN {$wpdb->posts} AS parent ON posts.post_parent = parent.ID";
		}

		$query['join'] = implode(' ', $joins);
		$query['where'] = "
			WHERE posts.post_type IN ('".implode("','", $args['order_types'])."')
			";

		if (!empty($orderStatus)) {
			/*$query['where'] .= "
				AND posts.post_status IN ( '".implode("','", $orderStatus)."')
			";*/
		}

		if ($args['filter_range']) {
			$query['where'] .= "
				AND posts.post_date >= '".date('Y-m-d', $this->range['start'])."'
				AND posts.post_date < '".date('Y-m-d', strtotime('+1 DAY', $this->range['end']))."'
			";
		}

		foreach ($args['data'] as $key => $value) {
			if ($value['type'] == 'meta') {
				$query['where'] .= " AND meta_{$key}.meta_key = '{$key}'";
			}
		}

		if (!empty($args['where_meta'])) {
			$relation = isset($where_meta['relation']) ? $where_meta['relation'] : 'AND';
			$query['where'] .= " AND (";

			foreach ($args['where_meta'] as $index => $value) {
				if (!is_array($value)) {
					continue;
				}

				$key = is_array($value['meta_key']) ? $value['meta_key'][0].'_array' : $value['meta_key'];
				if (strtolower($value['operator']) == 'in') {
					if (is_array($value['meta_value'])) {
						$value['meta_value'] = implode("','", $value['meta_value']);
					}

					if (!empty($value['meta_value'])) {
						$where_value = "IN ('{$value['meta_value']}')";
					}
				} else {
					$where_value = "{$value['operator']} '{$value['meta_value']}'";
				}

				if (!empty($where_value)) {
					if ($index > 0) {
						$query['where'] .= ' '.$relation;
					}

					if (is_array($value['meta_key'])) {
						$query['where'] .= " ( meta_{$key}.meta_key IN ('".implode("','", $value['meta_key'])."')";
					} else {
						$query['where'] .= " ( meta_{$key}.meta_key = '{$value['meta_key']}'";
					}

					$query['where'] .= " AND meta_{$key}.meta_value {$where_value} )";
				}
			}

			$query['where'] .= ")";
		}

		foreach ($args['where'] as $value) {
			if (strtolower($value['operator']) == 'in') {
				if (is_array($value['value'])) {
					$value['value'] = implode("','", $value['value']);
				}

				if (!empty($value['value'])) {
					$where_value = "IN ('{$value['value']}')";
				}
			} else {
				$where_value = "{$value['operator']} '{$value['value']}'";
			}

			if (!empty($where_value)) {
				$query['where'] .= " AND {$value['key']} {$where_value}";
			}
		}

		if ($args['group_by']) {
			$query['group_by'] = "GROUP BY {$args['group_by']}";
		}

		if ($args['order_by']) {
			$query['order_by'] = "ORDER BY {$args['order_by']}";
		}

		if ($args['limit']) {
			$query['limit'] = "LIMIT {$args['limit']}";
		}

		$query = $this->wp->applyFilters('jigoshop/admin/reports/get_order_report_query', $query);
		$query = implode(' ', $query);
		$queryHash = md5($args['query_type'].$query);
		$cachedResults = $this->wp->getTransient(strtolower(get_class($this)));

		if ($args['debug']) {
			echo '<pre>';
			print_r($query);
			echo '</pre>';
		}

		$args['debug'] = true;
		if ($args['debug'] || $args['nocache'] || false === $cachedResults || !isset($cachedResults[$queryHash])) {
			$cachedResults[$queryHash] = apply_filters('jigoshop_reports_get_order_report_data', $wpdb->{$args['query_type']}($query), $args['data']);

			// Process results
			foreach ($args['data'] as $key => $value) {
				if (!isset($value['process']) || $value['process'] !== true) {
					continue;
				}

				switch ($value['name']) {
					case 'order_data':
						$results = array();
						foreach ($cachedResults[$queryHash] as $item) {
							if (!isset($results[$item->post_date])) {
								$result = new \stdClass;
								$result->post_date = $item->post_date;
								$result->total_sales = 0.0;
								$result->total_shipping = 0.0;
								$result->total_tax = 0.0;
								$result->total_shipping_tax = 0.0;
								$results[$item->post_date] = $result;
							}

							$data = maybe_unserialize($item->order_data);
							$results[$item->post_date]->total_sales += $data['order_total'];
							$results[$item->post_date]->total_shipping += $data['order_shipping'];
							$results[$item->post_date]->total_tax += $data['order_tax_no_shipping_tax'];
							$results[$item->post_date]->total_shipping_tax += $data['order_shipping_tax'];
						}

						$cachedResults[$queryHash] = $results;
						break;
					case 'order_item_count':
						$results = array();
						foreach ($cachedResults[$queryHash] as $item) {
							if (!isset($results[$item->post_date])) {
								$result = new \stdClass;
								$result->post_date = $item->post_date;
								$result->order_item_count = 0;
								$results[$item->post_date] = $result;
							}

							$data = maybe_unserialize($item->order_item_count);
							$data = $this->filterItem($data, $value);
							$results[$item->post_date]->order_item_count += count($data);
						}

						$cachedResults[$queryHash] = $results;
						break;
					case 'order_item_quantity':
						$results = array();
						foreach ($cachedResults[$queryHash] as $item) {
							if (!isset($results[$item->post_date])) {
								$result = new \stdClass;
								$result->post_date = $item->post_date;
								$result->order_item_quantity = 0;
								$results[$item->post_date] = $result;
							}

							$data = maybe_unserialize($item->order_item_quantity);
							$data = $this->filterItem($data, $value);
							$results[$item->post_date]->order_item_quantity += array_sum(array_map(function($item){
								return isset($item['qty']) ? $item['qty'] : 0;
							}, $data));
						}

						$cachedResults[$queryHash] = $results;
						break;
					case 'discount_amount':
						$results = array();
						foreach ($cachedResults[$queryHash] as $item) {
							if (!isset($results[$item->post_date])) {
								$result = new \stdClass;
								$result->post_date = $item->post_date;
								$result->discount_amount = 0.0;
								$result->coupons_used = 0;
								$results[$item->post_date] = $result;
							}


							$data = maybe_unserialize($item->discount_amount);
							$data = $this->filterItem($data, $value);
							if (!empty($data) && isset($data['order_discount_coupons'])) {
								if(!empty($this->coupon_codes[0])){
									$coupon_code = $this->coupon_codes[0];
									$results[$item->post_date]->coupons_used += array_sum(array_map(function($coupon) use ($coupon_code){
										return $coupon['code'] == $coupon_code ? 1 : 0;
									}, $data['order_discount_coupons']));
									$results[$item->post_date]->discount_amount += array_sum(array_map(function($coupon) use ($coupon_code){
										return $coupon['code'] == $coupon_code ? $coupon['amount'] : 0;
									}, $data['order_discount_coupons']));

								} else {
									$results[$item->post_date]->coupons_used += count($data['order_discount_coupons']);

									foreach ($data['order_discount_coupons'] as $coupon) {
										if(isset($coupon['amount'])){
											$results[$item->post_date]->discount_amount += $coupon['amount'];
										}
									}
								}
							}
						}

						$cachedResults[$queryHash] = $results;
						break;
					case 'order_coupons':
						$coupons = array();
						$results = array();
						foreach ($cachedResults[$queryHash] as $item) {
							if (!isset($results[$item->post_date])) {
								$coupons[$item->post_date] = array();
								$results[$item->post_date] = new \stdClass();
								$results[$item->post_date]->post_date = $item->post_date;
								$results[$item->post_date]->coupons = array();
								$results[$item->post_date]->usage = array();
							}

							$data = maybe_unserialize($item->order_coupons);
							if(isset($data['order_discount_coupons']) && !empty($data['order_discount_coupons'])) {
								foreach ($data['order_discount_coupons'] as $coupon) {
									if(isset($coupon['code'])) {
										if (!in_array($coupon['code'], $coupons[$item->post_date])) {
											$results[$item->post_date]->coupons[] = $coupon;
											$results[$item->post_date]->usage[$coupon['code']] = 1;
											$coupons[$item->post_date][] = $coupon['code'];
										} else {
											$results[$item->post_date]->usage[$coupon['code']] += 1;
										}
									}
								}
							}
						}

						$cachedResults[$queryHash] = $results;
						break;
					case 'order_item_amount':
						$results = array();
						foreach ($cachedResults[$queryHash] as $item) {
							if (!isset($results[$item->post_date])) {
								$result = new \stdClass;
								$result->post_date = $item->post_date;
								$result->order_item_amount = 0.0;
								$results[$item->post_date] = $result;
							}

							$data = maybe_unserialize($item->order_item_amount);
							$data = $this->filterItem($data, $value);
							$results[$item->post_date]->order_item_amount += array_sum(array_map(function($product){
								return $product['qty'] * $product['cost'];
							}, $data));
						}

						$cachedResults[$queryHash] = $results;
						break;
					case 'category_data':
						$results = array();
						foreach ($cachedResults[$queryHash] as $item) {
							$data = maybe_unserialize($item->category_data);
							$data = $this->filterItem($data, $value);
							foreach ($data as $product) {
								if (!isset($results[$item->post_date][$product['id']])) {
									$result = new \stdClass;
									$result->product_id = $product['id'];
									$result->order_item_qty = 0;
									$result->order_item_total = 0;

									$result->post_date = $item->post_date;

									$results[$item->post_date][$product['id']] = $result;
								}

								$results[$item->post_date][$product['id']]->order_item_qty += $product['qty'];
								$results[$item->post_date][$product['id']]->order_item_total += $product['qty'] * $product['cost'];
							}
						}

						$cachedResults[$queryHash] = $results;
						break;
					case 'top_products':
						$results = array();
						foreach ($cachedResults[$queryHash] as $item) {
							$data = maybe_unserialize($item->top_products);
							$data = $this->filterItem($data, $value);
							foreach ($data as $product) {
								if (!isset($results[$product['id']])) {
									$result = new \stdClass;
									$result->product_id = $product['id'];
									$result->order_item_qty = 0;
									$result->order_item_total = 0;

									if (isset($item->post_date)) {
										$result->post_date = $item->post_date;
									}

									$results[$product['id']] = $result;
								}

								$results[$product['id']]->order_item_qty += $product['qty'];
								$results[$product['id']]->order_item_total += $product['qty'] * $product['cost'];
							}
						}

						if (isset($value['order'])) {
							switch($value['order']) {
								case 'most_sold':
									usort($results, function($a, $b){
										return $b->order_item_qty - $a->order_item_qty;
									});
									break;
								case 'most_earned':
									usort($results, function($a, $b){
										return $b->order_item_total - $a->order_item_total;
									});
									break;
							}
						}

						if (isset($value['limit'])) {
							$results = array_slice($results, 0, $value['limit']);
						}

						$cachedResults[$queryHash] = $results;
						break;
				}
			}

			set_transient(strtolower(get_class($this)), $cachedResults, DAY_IN_SECONDS);
		}

		return $cachedResults[$queryHash];
	}

	/**
	 * Converts array to std Object
	 *
	 * @param array $array
	 *
	 * @return object
	 */
	protected function arrayToObject(array $array)
	{
		return (object) $array;
	}
}