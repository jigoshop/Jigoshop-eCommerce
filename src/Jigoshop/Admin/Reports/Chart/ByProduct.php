<?php
/**
 * Created by PhpStorm.
 * User: Borbis Media
 * Date: 2015-11-17
 * Time: 11:57
 */

namespace Jigoshop\Admin\Reports\Chart;

use Jigoshop\Admin\Reports;
use Jigoshop\Admin\Reports\Chart;
use Jigoshop\Core\Options;
use Jigoshop\Helper\Currency;
use Jigoshop\Helper\Product;
use Jigoshop\Helper\Render;
use Jigoshop\Helper\Scripts;
use WPAL\Wordpress;

class ByProduct extends Chart
{
	private $chartColours = array();
	private $productIds = array();
	private $productTitles = array();
	private $reportData;

	public function __construct(Wordpress $wp, Options $options, $currentRange)
	{
		parent::__construct($wp, $options, $currentRange);
		if (isset($_GET['product_ids']) && is_array($_GET['product_ids'])) {
			$this->productIds = array_filter(array_map('absint', $_GET['product_ids']));
		} elseif (isset($_GET['product_ids'])) {
			$this->productIds = array_filter(array(absint($_GET['product_ids'])));
		}
		// Prepare data for report
		$this->calculateCurrentRange();
		$this->getReportData();
		$this->getChartColors();

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
		if (!$this->productIds) {
			return array();
		}

		$legend = array();

		$data = $this->getReportData();
		$totalSales = array_sum(array_map(function ($item){
			return $item->order_item_amount;
		}, $data->orderItemAmounts));
		$totalItems = array_sum(array_map(function ($item){
			return $item->order_item_count;
		}, $data->orderItemCounts));
		$totalQuantity = array_sum(array_map(function ($item){
			return $item->order_item_quantity;
		}, $data->orderItemQuantity));

		$legend[] = array(
			'title' => sprintf(__('%s sales for the selected items', 'jigoshop'), '<strong>'.Product::formatPrice($totalSales).'</strong>'),
			'color' => $this->chartColours['sales_amount'],
			'highlight_series' => 2
		);

		$legend[] = array(
			'title' => sprintf(__('%s purchases for the selected items', 'jigoshop'), '<strong>'.$totalItems.'</strong>'),
			'color' => $this->chartColours['item_count'],
			'highlight_series' => 1
		);

		$legend[] = array(
			'title' => sprintf(__('%s purchased quantity', 'jigoshop'), '<strong>'.$totalQuantity.'</strong>'),
			'color' => $this->chartColours['item_quantity'],
			'highlight_series' => 0
		);

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
		$this->reportData->orderItemQuantity = $this->getOrderReportData(array(
			'data' => array(
				'order_items' => array(
					'type' => 'meta',
					'name' => 'order_item_quantity',
					'process' => true,
					'where' => array(
						'type' => 'item_id',
						'keys' => array('id', 'variation_id'),
						'value' => $this->productIds,
					),
				),
				'post_date' => array(
					'type' => 'post_data',
					'function' => '',
					'name' => 'post_date'
				),
			),
			'order_types' => array('shop_order'),
			'query_type' => 'get_results',
			'filter_range' => true
		));
		$this->reportData->orderItemCounts = $this->getOrderReportData(array(
			'data' => array(
				'order_items' => array(
					'type' => 'meta',
					'name' => 'order_item_count',
					'process' => true,
					'where' => array(
						'type' => 'item_id',
						'keys' => array('id', 'variation_id'),
						'value' => $this->productIds,
					),
				),
				'post_date' => array(
					'type' => 'post_data',
					'function' => '',
					'name' => 'post_date'
				),
			),
			'order_types' => array('shop_order'),
			'query_type' => 'get_results',
			'filter_range' => true
		));

		$this->reportData->orderItemAmounts = $this->getOrderReportData(array(
			'data' => array(
				'order_items' => array(
					'type' => 'meta',
					'name' => 'order_item_amount',
					'process' => true,
					'where' => array(
						'type' => 'item_id',
						'keys' => array('id', 'variation_id'),
						'value' => $this->productIds,
					),
				),
				'post_date' => array(
					'type' => 'post_data',
					'function' => '',
					'name' => 'post_date'
				),
			),
			'order_types' => array('shop_order'),
			'query_type' => 'get_results',
			'filter_range' => true,
		));
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

		Render::output('admin/reports/chart', array(
			/** TODO This is ugly... */
			'current_type' => 'by_product',
			'ranges' => $ranges,
			'current_range' => $this->currentRange,
			'legends' => $this->getChartLegend(),
			'widgets' => $this->getChartWidgets(),
			'group_by' => $this->chartGroupBy
		));
	}

	/**
	 * [get_chart_widgets description]
	 *
	 * @return array
	 */
	public function getChartWidgets()
	{
		$widgets = array();

		if (!empty($this->productIds)) {
			$widgets[] = array(
				'title' => __('Showing reports for:', 'jigoshop'),
				'callback' => array($this, 'current_filters')
			);
		}

		$widgets[] = array(
			'title' => '',
			'callback' => array($this, 'products_widget')
		);

		return $widgets;
	}

	/**
	 * Show current filters
	 */
	public function current_filters()
	{
		$this->productTitles = array();
		foreach ($this->productIds as $productId) {
			$wpdb = $this->wp - getWPDB();
			$title = $wpdb->get_row($wpdb->prepare("SELECT post_title FROM $wpdb->posts WHERE ID = %s"), $productId);

			if ($title) {
				$this->productTitles[$productId] = $title;
			} else {
				$this->productTitles[$productId] = '#'.$productId;
			}
		}

		echo '<p>'.' <strong>'.implode(', ', $this->productTitles).'</strong></p>';
		echo '<p><a class="button" href="'.esc_url(remove_query_arg('product_ids')).'">'.__('Reset', 'jigoshop').'</a></p>';
	}

	/**
	 * Product selection
	 */
	public function products_widget()
	{
		?>
		<h4 class="section_title"><span><?php _e('Product Search', 'jigoshop'); ?></span></h4>
		<div class="section">
			<form method="GET">
				<div>
					<input type="hidden" class="jigoshop-product-search" style="width:203px;" name="product_ids[]"
					       data-placeholder="<?php _e('Search for a product&hellip;', 'jigoshop'); ?>" data-action="jigoshop_json_search_products_and_variations"/>
					<input type="submit" class="submit button" value="<?php _e('Show', 'jigoshop'); ?>"/>
					<input type="hidden" name="range" value="<?php if (!empty($_GET['range'])) echo esc_attr($_GET['range']) ?>"/>
					<input type="hidden" name="start_date" value="<?php if (!empty($_GET['start_date'])) echo esc_attr($_GET['start_date']) ?>"/>
					<input type="hidden" name="end_date" value="<?php if (!empty($_GET['end_date'])) echo esc_attr($_GET['end_date']) ?>"/>
					<input type="hidden" name="page" value="<?php if (!empty($_GET['page'])) echo esc_attr($_GET['page']) ?>"/>
					<input type="hidden" name="tab" value="<?php if (!empty($_GET['tab'])) echo esc_attr($_GET['tab']) ?>"/>
					<input type="hidden" name="report" value="<?php if (!empty($_GET['report'])) echo esc_attr($_GET['report']) ?>"/>
				</div>
			</form>
		</div>
		<h4 class="section_title"><span><?php _e('Top Sellers', 'jigoshop'); ?></span></h4>
		<div class="section">
			<table cellspacing="0">
				<?php
				$top_sellers = $this->getOrderReportData(array(
					'data' => array(
						'order_items' => array(
							'type' => 'meta',
							'name' => 'top_products',
							'process' => true,
							'limit' => 12,
							'order' => 'most_sold',
						),
					),
					'order_types' => array('shop_order'),
					'query_type' => 'get_results',
					'filter_range' => true,
				));

				if ($top_sellers) {
					foreach ($top_sellers as $product) {
						echo '<tr class="'.(in_array($product->product_id, $this->productIds) ? 'active' : '').'">
							<td class="count">'.$product->order_item_qty.'</td>
							<td class="name"><a href="'.esc_url(add_query_arg('product_ids', $product->product_id)).'">'.get_the_title($product->product_id).'</a></td>
							<td class="sparkline">'.$this->sales_sparkline($product->product_id, 7, 'count').'</td>
						</tr>';
					}
				} else {
					echo '<tr><td colspan="3">'.__('No products found in range', 'jigoshop').'</td></tr>';
				}
				?>
			</table>
		</div>
		<h4 class="section_title"><span><?php _e('Top Freebies', 'jigoshop'); ?></span></h4>
		<div class="section">
			<table cellspacing="0">
				<?php
				$top_freebies = $this->getOrderReportData(array(
					'data' => array(
						'order_items' => array(
							'type' => 'meta',
							'name' => 'top_products',
							'process' => true,
							'where' => array(
								'type' => 'comparison',
								'key' => 'cost',
								'value' => '0',
								'operator' => '0'
							)
						),
					),
					'order_types' => array('shop_order'),
					'query_type' => 'get_results',
					'limit' => 12,
					'nocache' => true
				));

				if ($top_freebies) {
					foreach ($top_freebies as $product) {
						echo '<tr class="'.(in_array($product->product_id, $this->productIds) ? 'active' : '').'">
							<td class="count">'.$product->order_item_qty.'</td>
							<td class="name"><a href="'.esc_url(add_query_arg('product_ids', $product->product_id)).'">'.get_the_title($product->product_id).'</a></td>
							<td class="sparkline">'.$this->sales_sparkline($product->product_id, 7, 'count').'</td>
						</tr>';
					}
				} else {
					echo '<tr><td colspan="3">'.__('No products found in range', 'jigoshop').'</td></tr>';
				}
				?>
			</table>
		</div>
		<h4 class="section_title"><span><?php _e('Top Earners', 'jigoshop'); ?></span></h4>
		<div class="section">
			<table cellspacing="0">
				<?php
				$top_earners = $this->getOrderReportData(array(
					'data' => array(
						'order_items' => array(
							'type' => 'meta',
							'name' => 'top_products',
							'process' => true,
							'limit' => 12,
							'order' => 'most_earned',
						),
					),
					'order_types' => array('shop_order'),
					'query_type' => 'get_results',
					'filter_range' => true
				));

				if ($top_earners) {
					foreach ($top_earners as $product) {
						echo '<tr class="'.(in_array($product->product_id, $this->productIds) ? 'active' : '').'">
							<td class="count">'.jigoshop_price($product->order_item_total).'</td>
							<td class="name"><a href="'.esc_url(add_query_arg('product_ids', $product->product_id)).'">'.get_the_title($product->product_id).'</a></td>
							<td class="sparkline">'.$this->salesSparkline($product->product_id, 7, 'sales').'</td>
						</tr>';
					}
				} else {
					echo '<tr><td colspan="3">'.__('No products found in range', 'jigoshop').'</td></tr>';
				}
				?>
			</table>
		</div>
		<script type="text/javascript">
			jQuery(function($) {
				$('.section_title').click(function() {
					var next_section = $(this).next('.section');
					if ($(next_section).is(':visible'))
						return false;
					$('.section:visible').slideUp();
					$('.section_title').removeClass('open');
					$(this).addClass('open').next('.section').slideDown();
					return false;
				});
				$('.section').slideUp(100, function() {
					<?php if (empty($this->productIds)): ?>
					$('.section_title:eq(1)').click();
					<?php endif; ?>
				});
			});
		</script>
		<?php
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
		// Prepare data for report
		$orderItemCounts = $this->prepareChartData($this->reportData->orderItemCounts, 'post_date', 'order_item_count', $this->chartInterval, $this->range['start'], $this->chartGroupBy);
		$orderItemAmounts = $this->prepareChartData($this->reportData->orderItemAmounts, 'post_date', 'order_item_amount', $this->chartInterval, $this->range['start'], $this->chartGroupBy);
		$orderItemQuantity = $this->prepareChartData($this->reportData->orderItemQuantity, 'post_date', 'order_item_quantity', $this->chartInterval, $this->range['start'], $this->chartGroupBy);

		$data = array();
		$data['series'] = array();
		$data['series'][] = $this->arrayToObject(array(
			'label' => __('Sold quantity', 'jigoshop'),
			'data' => array_values($orderItemQuantity),
			'color' => $this->chartColours['item_quantity'],
			'bars' => $this->arrayToObject(array(
				'fillColor' => $this->chartColours['item_quantity'],
				'fill' => true,
				'show' => true,
				'lineWidth' => 0,
				'align' => 'left',
				'barWidth' => $this->barwidth * 0.25,
			)),
			'shadowSize' => 0,
			'hoverable' => false
		));
		$data['series'][] = $this->arrayToObject(array(
			'label' => __('Number of items sold', 'jigoshop'),
			'data' => array_values($orderItemCounts),
			'color' => $this->chartColours['item_count'],
			'bars' => $this->arrayToObject(array(
				'fillColor' => $this->chartColours['item_count'],
				'fill' => true,
				'show' => true,
				'lineWidth' => 0,
				'align' => 'right',
				'barWidth' => $this->barwidth * 0.25,
			)),
			'shadowSize' => 0,
			'hoverable' => false
		));
		$data['series'][] = $this->arrayToObject(array(
			'label' => __('Sales amount', 'jigoshop'),
			'data' => array_values($orderItemAmounts),
			'yaxis' => 2,
			'color' => $this->chartColours['sales_amount'],
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
			'append_tooltip' => Currency::symbol(),
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
					'timeformat' => $this->chartGroupby == 'hour' ? '%H' : $this->chartGroupBy == 'day' ? '%d %b' : '%b',
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
					'font' => $this->arrayToObject(array('color' => '#aaa')),
				)),
				$this->arrayToObject(array(
					'position' => 'right',
					'min' => 0,
					'tickDecimals' => 2,
					'alignTicksWithAxis' => 1,
					'color' => 'transparent',
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

	private function getChartColors()
	{
		$this->chartColours = $this->wp->applyFilters('jigoshop/admin/reports/by_product/chart_colors', array(
			'sales_amount' => '#3498db',
			'item_count' => '#d4d9dc',
			'item_quantity' => '#ecf0f1'
		));
	}
}