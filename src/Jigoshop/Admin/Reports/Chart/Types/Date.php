<?php

namespace Jigoshop\Admin\Reports\Chart\Types;

use Jigoshop\Admin\Reports\Chart;

class Date extends Chart
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
				$average_sales_title = sprintf(__('%s average sales per hour', 'jigoshop'), '<strong>'.jigoshop_price($data->average_sales).'</strong>');
				break;
			case 'day' :
				/** @noinspection PhpUndefinedFieldInspection */
				$average_sales_title = sprintf(__('%s average daily sales', 'jigoshop'), '<strong>'.jigoshop_price($data->average_sales).'</strong>');
				break;
			case 'month' :
			default :
				/** @noinspection PhpUndefinedFieldInspection */
				$average_sales_title = sprintf(__('%s average monthly sales', 'jigoshop'), '<strong>'.jigoshop_price($data->average_sales).'</strong>');
				break;
		}

		/** @noinspection PhpUndefinedFieldInspection */
		$legend[] = array(
			'title' => sprintf(__('%s gross sales in this period', 'jigoshop'), '<strong>'.jigoshop_price($data->total_sales).'</strong>'),
			'placeholder' => __('This is the sum of the order totals including shipping and taxes.', 'jigoshop'),
			'color' => $this->chartColours['sales_amount'],
			'highlight_series' => 5
		);
		/** @noinspection PhpUndefinedFieldInspection */
		$legend[] = array(
			'title' => sprintf(__('%s net sales in this period', 'jigoshop'), '<strong>'.jigoshop_price($data->net_sales).'</strong>'),
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
			'title' => sprintf(__('%s charged for shipping', 'jigoshop'), '<strong>'.jigoshop_price($data->total_shipping).'</strong>'),
			'color' => $this->chartColours['shipping_amount'],
			'highlight_series' => 4
		);
		/** @noinspection PhpUndefinedFieldInspection */
		$legend[] = array(
			'title' => sprintf(__('%s worth of coupons used', 'jigoshop'), '<strong>'.jigoshop_price($data->total_coupons).'</strong>'),
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
		$this->reportData = new stdClass;

		$this->reportData->orders = (array)$this->get_orderReportData(array(
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

		$this->reportData->totalSales = jigoshop_format_decimal(array_sum(wp_list_pluck($this->reportData->orders, 'total_sales')), 2);
		$this->reportData->totalTax = jigoshop_format_decimal(array_sum(wp_list_pluck($this->reportData->orders, 'total_tax')), 2);
		$this->reportData->totalShipping = jigoshop_format_decimal(array_sum(wp_list_pluck($this->reportData->orders, 'total_shipping')), 2);
		$this->reportData->totalShippingTax = jigoshop_format_decimal(array_sum(wp_list_pluck($this->reportData->orders, 'total_shipping_tax')), 2);
		$this->reportData->totalCoupons = array_sum(wp_list_pluck($this->reportData->coupons, 'discount_amount'));
		$this->reportData->totalOrders = absint(array_sum(wp_list_pluck($this->reportData->orderCounts, 'count')));
		$this->reportData->totalItems = absint(array_sum(wp_list_pluck($this->reportData->orderItems, 'order_item_count')) * -1);
		$this->reportData->averageSales = jigoshop_format_decimal($this->reportData->totalSales / ($this->chartInterval + 1), 2);
		$this->reportData->netSales = jigoshop_format_decimal($this->reportData->totalSales - $this->reportData->totalShipping - $this->reportData->totalTax - $this->reportData->totalShippingTax, 2);
	}

	/**
	 * Output the report
	 */
	public function output()
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
		$orderCounts = $this->prepareChartData($this->report_data->orderCounts, 'post_date', 'count', $this->chartInterval, $this->range['start'], $this->chartGroupBy);
		$orderItemCounts = $this->prepareChartData($this->report_data->orderItems, 'post_date', 'order_item_count', $this->chartInterval, $this->range['start'], $this->chartGroupBy);
		$orderAmounts = $this->prepareChartData($this->report_data->orders, 'post_date', 'total_sales', $this->chartInterval, $this->range['start'], $this->chartGroupBy);
		$couponAmounts = $this->prepareChartData($this->report_data->coupons, 'post_date', 'discount_amount', $this->chartInterval, $this->range['start'], $this->chartGroupBy);
		$shippingAmounts = $this->prepareChartData($this->report_data->orders, 'post_date', 'total_shipping', $this->chartInterval, $this->range['start'], $this->chartGroupBy);
		$shippingTaxAmounts = $this->prepareChartData($this->report_data->orders, 'post_date', 'total_shipping_tax', $this->chartInterval, $this->range['start'], $this->chartGroupBy);
		$taxAmounts = $this->prepareChartData($this->report_data->orders, 'post_date', 'total_tax', $this->chartInterval, $this->range['start'], $this->chartGroupBy);

		$net_order_amounts = array();

		foreach ($orderAmounts as $orderAmountKey => $orderAmountValue) {
			$net_order_amounts[$orderAmountKey] = $orderAmountValue;
			$net_order_amounts[$orderAmountKey][1] = $net_order_amounts[$orderAmountKey][1] - $shippingAmounts[$orderAmountKey][1] - $shippingTaxAmounts[$orderAmountKey][1] - $taxAmounts[$orderAmountKey][1];
		}

		// Encode in json format
		$chart_data = json_encode(array(
			'order_counts' => array_values($orderCounts),
			'order_item_counts' => array_values($orderItemCounts),
			'order_amounts' => array_map(array(
				$this,
				'roundChartTotals'
			), array_values($orderAmounts)),
			'net_order_amounts' => array_map(array(
				$this,
				'roundChartTotals'
			), array_values($net_order_amounts)),
			'shipping_amounts' => array_map(array(
				$this,
				'roundChartTotals'
			), array_values($shippingAmounts)),
			'coupon_amounts' => array_map(array(
				$this,
				'roundChartTotals'
			), array_values($couponAmounts)),
		));
		?>
		<div class="chart-container">
			<div class="chart-placeholder main"></div>
		</div>
		<script type="text/javascript">
			var main_chart;
			jQuery(function(){
				var order_data = jQuery.parseJSON('<?php echo $chart_data; ?>');
				var drawGraph = function(highlight){
					var series = [
						{
							label: "<?php echo esc_js( __( 'Number of items sold', 'jigoshop' ) ) ?>",
							data: order_data.order_item_counts,
							color: '<?php echo $this->chart_colours['item_count']; ?>',
							bars: {
								fillColor: '<?php echo $this->chart_colours['item_count']; ?>',
								fill: true,
								show: true,
								lineWidth: 0,
								align: 'left',
								barWidth: 0<?php echo $this->barwidth; ?> * 0.25
							},
							shadowSize: 0,
							hoverable: false
						},
						{
							label: "<?php echo esc_js( __( 'Number of orders', 'jigoshop' ) ) ?>",
							data: order_data.order_counts,
							color: '<?php echo $this->chart_colours['order_count']; ?>',
							bars: {
								fillColor: '<?php echo $this->chart_colours['order_count']; ?>',
								fill: true,
								show: true,
								lineWidth: 0,
								align: 'right',
								barWidth: 0<?php echo $this->barwidth; ?> * 0.25
							},
							shadowSize: 0,
							hoverable: false
						},
						{
							label: "<?php echo esc_js( __( 'Average sales amount', 'jigoshop' ) ) ?>",
							data: [[<?php echo min( array_keys( $orderAmounts ) ); ?>, <?php echo $this->report_data->average_sales; ?>], [<?php echo max( array_keys( $orderAmounts ) ); ?>, <?php echo $this->report_data->average_sales; ?>]],
							yaxis: 2,
							color: '<?php echo $this->chart_colours['average']; ?>',
							points: {show: false},
							lines: {show: true, lineWidth: 2, fill: false},
							shadowSize: 0,
							hoverable: false
						},
						{
							label: "<?php echo esc_js( __( 'Coupon amount', 'jigoshop' ) ) ?>",
							data: order_data.coupon_amounts,
							yaxis: 2,
							color: '<?php echo $this->chart_colours['coupon_amount']; ?>',
							points: {show: true, radius: 5, lineWidth: 2, fillColor: '#fff', fill: true},
							lines: {show: true, lineWidth: 2, fill: false},
							shadowSize: 0,
							<?php echo $this->get_currency_tooltip(); ?>
						},
						{
							label: "<?php echo esc_js( __( 'Shipping amount', 'jigoshop' ) ) ?>",
							data: order_data.shipping_amounts,
							yaxis: 2,
							color: '<?php echo $this->chart_colours['shipping_amount']; ?>',
							points: {show: true, radius: 5, lineWidth: 2, fillColor: '#fff', fill: true},
							lines: {show: true, lineWidth: 2, fill: false},
							shadowSize: 0,
							prepend_tooltip: "<?php echo get_jigoshop_currency_symbol(); ?>"
						},
						{
							label: "<?php echo esc_js( __( 'Gross Sales amount', 'jigoshop' ) ) ?>",
							data: order_data.order_amounts,
							yaxis: 2,
							color: '<?php echo $this->chart_colours['sales_amount']; ?>',
							points: {show: true, radius: 5, lineWidth: 2, fillColor: '#fff', fill: true},
							lines: {show: true, lineWidth: 2, fill: false},
							shadowSize: 0,
							<?php echo $this->get_currency_tooltip(); ?>
						},
						{
							label: "<?php echo esc_js( __( 'Net Sales amount', 'jigoshop' ) ) ?>",
							data: order_data.net_order_amounts,
							yaxis: 2,
							color: '<?php echo $this->chart_colours['net_sales_amount']; ?>',
							points: {show: true, radius: 6, lineWidth: 4, fillColor: '#fff', fill: true},
							lines: {show: true, lineWidth: 5, fill: false},
							shadowSize: 0,
							<?php echo $this->get_currency_tooltip(); ?>
						}
					];
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
		<?php
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
			return jigoshop_format_decimal($amount, '');
		}
	}
}