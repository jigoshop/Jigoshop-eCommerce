<?php

namespace Jigoshop\Admin\Reports\Chart;

use Jigoshop\Admin\Reports\Chart;
use Jigoshop\Core\Options;
use Jigoshop\Helper\Currency;
use Jigoshop\Helper\Product;
use WPAL\Wordpress;

class ByDate extends Chart
{
	public $chartColours = array();
	private $reportData;

	public function __construct(Wordpress $wp, Options $options, $currentRange)
	{
		parent::__construct($wp, $options, $currentRange);
	}

	/**
	 * Get the legend for the main chart sidebar
	 *
	 * @return array
	 */
	public function getChartLegend()
	{
		$legend = array();
		$data = $this->getReportData();

		switch ($this->chartGroupBy) {
			case 'hour' :
				/** @noinspection PhpUndefinedFieldInspection */
				$average_sales_title = sprintf(__('%s average sales per hour', 'jigoshop'), '<strong>'.Product::formatPrice($data->average_sales).'</strong>');
				break;
			case 'day' :
				/** @noinspection PhpUndefinedFieldInspection */
				$average_sales_title = sprintf(__('%s average daily sales', 'jigoshop'), '<strong>'.Product::formatPrice($data->average_sales).'</strong>');
				break;
			case 'month' :
			default :
				/** @noinspection PhpUndefinedFieldInspection */
				$average_sales_title = sprintf(__('%s average monthly sales', 'jigoshop'), '<strong>'.Product::formatPrice($data->average_sales).'</strong>');
				break;
		}

		/** @noinspection PhpUndefinedFieldInspection */
		$legend[] = array(
			'title' => sprintf(__('%s gross sales in this period', 'jigoshop'), '<strong>'.Product::formatPrice($data->total_sales).'</strong>'),
			'placeholder' => __('This is the sum of the order totals including shipping and taxes.', 'jigoshop'),
			'color' => $this->chartColours['sales_amount'],
			'highlight_series' => 5
		);
		/** @noinspection PhpUndefinedFieldInspection */
		$legend[] = array(
			'title' => sprintf(__('%s net sales in this period', 'jigoshop'), '<strong>'.Product::formatPrice($data->net_sales).'</strong>'),
			'placeholder' => __('This is the sum of the order totals excluding shipping and taxes.', 'jigoshop'),
			'color' => $this->chartColours['net_sales_amount'],
			'highlight_series' => 6
		);
		$legend[] = array(
			'title' => $average_sales_title,
			'color' => $this->chartColours['average'],
			'highlight_series' => 2
		);
		/** @noinspection PhpUndefinedFieldInspection */
		$legend[] = array(
			'title' => sprintf(__('%s orders placed', 'jigoshop'), '<strong>'.$data->total_orders.'</strong>'),
			'color' => $this->chartColours['order_count'],
			'highlight_series' => 1
		);

		/** @noinspection PhpUndefinedFieldInspection */
		$legend[] = array(
			'title' => sprintf(__('%s items purchased', 'jigoshop'), '<strong>'.$data->total_items.'</strong>'),
			'color' => $this->chartColours['item_count'],
			'highlight_series' => 0
		);

		/** @noinspection PhpUndefinedFieldInspection */
		$legend[] = array(
			'title' => sprintf(__('%s charged for shipping', 'jigoshop'), '<strong>'.Product::formatPrice($data->total_shipping).'</strong>'),
			'color' => $this->chartColours['shipping_amount'],
			'highlight_series' => 4
		);
		/** @noinspection PhpUndefinedFieldInspection */
		$legend[] = array(
			'title' => sprintf(__('%s worth of coupons used', 'jigoshop'), '<strong>'.Product::formatPrice($data->total_coupons).'</strong>'),
			'color' => $this->chartColours['coupon_amount'],
			'highlight_series' => 3
		);

		return $legend;
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
		$this->reportData = new \stdClass;

		$this->reportData->orders = (array)$this->getOrderReportData(array(
			'data' => array(
				'order_data' => array(
					'type' => 'meta',
					'name' => 'order_data',
					'process' => true,
				),
				'post_date' => array(
					'type' => 'post_data',
					'function' => '',
					'name' => 'post_date'
				),
			),
			'order_by' => 'post_date ASC',
			'query_type' => 'get_results',
			'filter_range' => true,
			'order_types' => array('shop_order'),
			'order_status' => $this->order_status,
		));

		$this->reportData->orderCounts = (array)$this->getOrderReportData(array(
			'data' => array(
				'ID' => array(
					'type' => 'post_data',
					'function' => 'COUNT',
					'name' => 'count',
					'distinct' => true,
				),
				'post_date' => array(
					'type' => 'post_data',
					'function' => '',
					'name' => 'post_date'
				)
			),
			'group_by' => $this->group_by_query,
			'order_by' => 'post_date ASC',
			'query_type' => 'get_results',
			'filter_range' => true,
			'order_types' => array('shop_order'),
			'order_status' => $this->order_status
		));

		$this->reportData->coupons = (array)$this->getOrderReportData(array(
			'data' => array(
				'order_data' => array(
					'type' => 'meta',
					'name' => 'discount_amount',
					'process' => true,
				),
				'post_date' => array(
					'type' => 'post_data',
					'function' => '',
					'name' => 'post_date'
				),
			),
			'order_by' => 'post_date ASC',
			'query_type' => 'get_results',
			'filter_range' => true,
			'order_types' => array('shop_order'),
			'order_status' => $this->order_status,
		));

		$this->reportData->orderItems = (array)$this->getOrderReportData(array(
			'data' => array(
				'order_items' => array(
					'type' => 'meta',
					'name' => 'order_item_count',
					'process' => true,
				),
				'post_date' => array(
					'type' => 'post_data',
					'function' => '',
					'name' => 'post_date'
				),
			),
			'order_by' => 'post_date ASC',
			'query_type' => 'get_results',
			'filter_range' => true,
			'order_types' => array('shop_order'),
			'order_status' => $this->order_status,
		));

		$this->reportData->totalSales = array_sum(wp_list_pluck($this->reportData->orders, 'total_sales'));
		$this->reportData->totalTax = array_sum(wp_list_pluck($this->reportData->orders, 'total_tax'));
		$this->reportData->totalShipping = array_sum(wp_list_pluck($this->reportData->orders, 'total_shipping'));
		$this->reportData->totalShippingTax = array_sum(wp_list_pluck($this->reportData->orders, 'total_shipping_tax'));
		$this->reportData->totalCoupons = array_sum(wp_list_pluck($this->reportData->coupons, 'discount_amount'));
		$this->reportData->totalOrders = absint(array_sum(wp_list_pluck($this->reportData->orderCounts, 'count')));
		$this->reportData->totalItems = absint(array_sum(wp_list_pluck($this->reportData->orderItems, 'order_item_count')) * -1);
		$this->reportData->averageSales = $this->reportData->totalSales / ($this->chartInterval + 1);
		$this->reportData->netSales = $this->reportData->totalSales - $this->reportData->totalShipping - $this->reportData->totalTax - $this->reportData->totalShippingTax;
	}

	/**
	 * Output the report
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

		$this->chartColours = array(
			'sales_amount' => '#b1d4ea',
			'net_sales_amount' => '#3498db',
			'average' => '#95a5a6',
			'order_count' => '#dbe1e3',
			'item_count' => '#ecf0f1',
			'shipping_amount' => '#5cc488',
			'coupon_amount' => '#f1c40f',
		);

		$this->calculateCurrentRange();
		$this->getChartLegend();
		$this->getMainChart();
	}

	/**
	 * [get_chart_widgets description]
	 *
	 * @return array
	 */
	public function getChartWidgets()
	{
		$widgets = array();

		$widgets[] = array(
			'title' => __('Order status filter', 'jigoshop'),
			'callback' => 'order_status_widget'
		);

		return $widgets;
	}

	/**
	 * Output an export link
	 */
	public function getExportButton()
	{
		?>
		<a href="#" download="report-<?php echo esc_attr($this->currentRange); ?>-<?php echo date_i18n('Y-m-d', current_time('timestamp')); ?>.csv" class="export_csv" data-export="chart" data-xaxes="<?php _e('Date', 'jigoshop'); ?>" data-exclude_series="2" data-groupby="<?php echo $this->chartGroupBy; ?>">
			<?php _e('Export CSV', 'jigoshop'); ?>
		</a>
		<?php
	}

	/**
	 * Get the main chart
	 *
	 * @return string
	 */
	public function getMainChart()
	{
		global $wp_locale;

		// Prepare data for report
		$orderCounts = $this->prepareChartData($this->reportData->orderCounts, 'post_date', 'count', $this->chartInterval, $this->range['start'], $this->chartGroupBy);
		$orderItemCounts = $this->prepareChartData($this->reportData->orderItems, 'post_date', 'order_item_count', $this->chartInterval, $this->range['start'], $this->chartGroupBy);
		$orderAmounts = $this->prepareChartData($this->reportData->orders, 'post_date', 'total_sales', $this->chartInterval, $this->range['start'], $this->chartGroupBy);
		$couponAmounts = $this->prepareChartData($this->reportData->coupons, 'post_date', 'discount_amount', $this->chartInterval, $this->range['start'], $this->chartGroupBy);
		$shippingAmounts = $this->prepareChartData($this->reportData->orders, 'post_date', 'total_shipping', $this->chartInterval, $this->range['start'], $this->chartGroupBy);
		$shippingTaxAmounts = $this->prepareChartData($this->reportData->orders, 'post_date', 'total_shipping_tax', $this->chartInterval, $this->range['start'], $this->chartGroupBy);
		$taxAmounts = $this->prepareChartData($this->reportData->orders, 'post_date', 'total_tax', $this->chartInterval, $this->range['start'], $this->chartGroupBy);

		$netOrderAmounts = array();

		foreach ($orderAmounts as $orderAmountKey => $orderAmountValue) {
			$netOrderAmounts[$orderAmountKey] = $orderAmountValue;
			$netOrderAmounts[$orderAmountKey][1] = $netOrderAmounts[$orderAmountKey][1] - $shippingAmounts[$orderAmountKey][1] - $shippingTaxAmounts[$orderAmountKey][1] - $taxAmounts[$orderAmountKey][1];
		}

		// Encode in json format
		$chartData = array(
			'order_counts' => array_values($orderCounts),
			'order_item_counts' => array_values($orderItemCounts),
			'order_amounts' => array_map(array(
				$this,
				'roundChartTotals'
			), array_values($orderAmounts)),
			'net_order_amounts' => array_map(array(
				$this,
				'roundChartTotals'
			), array_values($netOrderAmounts)),
			'shipping_amounts' => array_map(array(
				$this,
				'roundChartTotals'
			), array_values($shippingAmounts)),
			'coupon_amounts' => array_map(array(
				$this,
				'roundChartTotals'
			), array_values($couponAmounts)),
		);
		$series = array();
		$series[] = $this->arrayToObject(array(
			'label' => esc_js( __('Number of items sold', 'jigoshop')),
			'data' => array_values($orderItemCounts),
			'color' => $this->chartColours['item_count'],
			'bars' => array(
				'fillColor' => $this->chartColours['item_count'],
				'fill' => true,
				'show' => true,
				'lineWidth' => 0,
				'align' => 'left',
				'barWidth' => $this->barwidth * 0.25,
			),
			'shadowSize' => 0,
			'hoverable' => false
		));
		$series[] = $this->arrayToObject(array(
			'label' => esc_js( __('Number of orders', 'jigoshop')),
			'data' => array_values($orderCounts),
			'color' => $this->chartColours['order_count'],
			'bars' => array(
				'fillColor' => $this->chartColours['order_count'],
					'fill' => true,
					'show' => true,
					'lineWidth' => 0,
					'align' => 'right',
					'barWidth' => $this->barwidth * 0.25,
			),
			'shadowSize' => 0,
			'hoverable' => false
		));
		$series[] = $this->arrayToObject(array(
			'label' => esc_js( __('Average sales amount', 'jigoshop')),
			'data' => array(
				array(min(array_keys(array_map(array($this,'roundChartTotals'), array_values($orderAmounts)))), $this->reportData->average_sales),
				array(max(array_keys(array_map(array($this,'roundChartTotals'), array_values($orderAmounts)))), $this->reportData->average_sales),
			),
			'yaxis' => 2,
			'color' => $this->chartColours['average'],
			'points' => $this->arrayToObject(array('show' => false)),
			'lines' => $this->arrayToObject(array('show' => true, 'lineWidth' => 2, 'fill' => false)),
			'shadowSize' => 0,
			'hoverable' => false
		));
		$series[] = $this->arrayToObject(array(
			'label' => esc_js( __('Coupon amount', 'jigoshop')),
			'data' => array_map(array($this,'roundChartTotals'), array_values($couponAmounts)),
			'yaxis' => 2,
			'color' => $this->chartColours['coupon_amount'],
			'points' => $this->arrayToObject(array('show' => true, 'radius' => 5, 'lineWidth' => 2, 'fillColor' => '#fff', 'fill' => true)),
			'lines' => $this->arrayToObject(array('show' => true, 'lineWidth' => 2, 'fill' => false)),
			'shadowSize' => 0,
			'hoverable' => false,
			'append_tooltip' => Currency::symbol(),
		));
		$series[] = $this->arrayToObject(array(
			'label' => esc_js( __('Shipping amount', 'jigoshop')),
			'data' => array_map(array($this,'roundChartTotals'), array_values($shippingAmounts)),
			'yaxis' => 2,
			'color' => $this->chartColours['shipping_amount'],
			'points' => $this->arrayToObject(array('show' => true, 'radius' => 5, 'lineWidth' => 2, 'fillColor' => '#fff', 'fill' => true)),
			'lines' => $this->arrayToObject(array('show' => true, 'lineWidth' => 2, 'fill' => false)),
			'shadowSize' => 0,
			'prepend_tooltip' => Currency::symbol(),
		));
		$series[] = $this->arrayToObject(array(
			'label' => esc_js( __('Gross Sales amount', 'jigoshop')),
			'data' => array_map(array($this,'roundChartTotals'), array_values($orderAmounts)),
			'yaxis' => 2,
			'color' => $this->chartColours['sales_amount'],
			'points' => $this->arrayToObject(array('show' => true, 'radius' => 5, 'lineWidth' => 2, 'fillColor' => '#fff', 'fill' => true)),
			'lines' => $this->arrayToObject(array('show' => true, 'lineWidth' => 2, 'fill' => false)),
			'shadowSize' => 0,
			'append_tooltip' => Currency::symbol(),
		));
		$series[] = $this->arrayToObject(array(
			'label' => esc_js( __('Net Sales amount', 'jigoshop')),
			'data' => array_map(array($this,'roundChartTotals'), array_values($netOrderAmounts)),
			'yaxis' => 2,
			'color' => $this->chartColours['net_sales_amount'],
			'points' => $this->arrayToObject(array('show' => true, 'radius' => 5, 'lineWidth' => 4, 'fillColor' => '#fff', 'fill' => true)),
			'lines' => $this->arrayToObject(array('show' => true, 'lineWidth' => 5, 'fill' => false)),
			'shadowSize' => 0,
			'append_tooltip' => Currency::symbol(),
		));
		echo '<script> var series = '.json_encode($series).'; console.log(series);</script>';


				/*
					if(highlight !== 'undefined' && series[highlight]){
						highlight_series = series[highlight];
						highlight_series.color = '#98c242';
						if(highlight_series.bars)
							highlight_series.bars.fillColor = '#98c242';
						if(highlight_series.lines){
							highlight_series.lines.lineWidth = 5;
						}
					}
					main_chart = jQuery.plot(
						jQuery('.chart-placeholder.main'),
						series,
						{
							legend: {
								show: false
							},
							grid: {
								color: '#aaa',
								borderColor: 'transparent',
								borderWidth: 0,
								hoverable: true
							},
							xaxes: [{
								color: '#aaa',
								position: "bottom",
								tickColor: 'transparent',
								mode: "time",
								timeformat: "<?php if ($this->chartGroupBy == 'hour') {echo '%H';} elseif ($this->chartGroupBy == 'day') {echo '%d %b';} else {echo '%b';} ?>",
								<?php if ($this->chartGroupBy == 'hour'): ?>
								min: 0,
								max: 24*3600000,
								<?php endif; ?>
								monthNames: <?php echo json_encode( array_values( $wp_locale->month_abbrev ) ) ?>,
								tickLength: 1,
								minTickSize: [1, "<?php echo $this->chartGroupBy; ?>"],
								font: {
									color: "#aaa"
								}
							}],
							yaxes: [
								{
									min: 0,
									minTickSize: 1,
									tickDecimals: 0,
									color: '#d4d9dc',
									font: {color: "#aaa"}
								},
								{
									position: "right",
									min: 0,
									tickDecimals: 2,
									alignTicksWithAxis: 1,
									autoscaleMargin: 0,
									color: 'transparent',
									font: {color: "#aaa"}
								}
							]
						}
					);
					jQuery('.chart-placeholder').resize();
				};

				drawGraph();
				jQuery('.highlight_series').hover(
					function(){
						drawGraph(jQuery(this).data('series'));
					},
					function(){
						drawGraph();
					}
				);
			});
		</script>
	*/
	}

	/**
	 * Round our totals correctly
	 *
	 * @param  string $amount
	 * @return string
	 */
	private function roundChartTotals($amount)
	{
		if (is_array($amount)) {
			return array_map(array($this, 'roundChartTotals'), $amount);
		} else {
			return $amount;
		}
	}
}