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

class ByProduct extends Chart
{
	private $chartColours = [];
	private $productIds = [];
	private $reportData;

	public function __construct(Wordpress $wp, Options $options, $currentRange)
	{
		parent::__construct($wp, $options, $currentRange);
		if (isset($_GET['product_ids']) && is_array($_GET['product_ids'])) {
			$this->productIds = array_filter(array_map('absint', $_GET['product_ids']));
		} elseif (isset($_GET['product_ids'])) {
			$this->productIds = explode(',', $_GET['product_ids']);
			$this->productIds = array_filter(array_map('absint', $this->productIds));
		}

		// Prepare data for report
		$this->calculateCurrentRange();
		$this->getReportData();
		$this->getChartColours();

		$wp->addAction('admin_enqueue_scripts', function () use ($wp){
			// Weed out all admin pages except the Jigoshop Settings page hits
			if (!in_array($wp->getPageNow(), ['admin.php', 'options.php'])) {
				return;
			}

			$screen = $wp->getCurrentScreen();
			if ($screen->base != 'jigoshop_page_'.Reports::NAME) {
				return;
			}
			Styles::add('jigoshop.vendors.select2', \JigoshopInit::getUrl().'/assets/css/vendors/select2.css', ['jigoshop.admin']);
			Scripts::add('jigoshop.vendors.select2', \JigoshopInit::getUrl().'/assets/js/vendors/select2.js', ['jigoshop.admin'], ['in_footer' => true]);
			Scripts::add('jigoshop.admin.reports.widget.product_search', \JigoshopInit::getUrl().'/assets/js/admin/reports/widget/product_search.js', [
				'jquery',
				'jigoshop.vendors.select2',
                'jigoshop.helpers.ajax_search'
            ], ['in_footer' => true]);
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
			return [];
		}

		$legend = [];

		$totalSales = array_sum(array_map(function ($item){
			return $item->order_item_amount;
		}, $this->reportData->orderItems));
		$totalItems = array_sum(array_map(function ($item){
			return $item->order_item_count;
		}, $this->reportData->orderItems));
		$totalQuantity = array_sum(array_map(function ($item){
			return $item->order_item_quantity;
		}, $this->reportData->orderItems));

		$legend[] = [
			'title' => sprintf(__('%s sales for the selected items', 'jigoshop-ecommerce'), '<strong>'.Product::formatPrice($totalSales).'</strong>'),
			'color' => $this->chartColours['sales_amount'],
			'highlight_series' => 2
        ];

		$legend[] = [
			'title' => sprintf(__('%s purchases for the selected items', 'jigoshop-ecommerce'), '<strong>'.$totalItems.'</strong>'),
			'color' => $this->chartColours['item_count'],
			'highlight_series' => 1
        ];

		$legend[] = [
			'title' => sprintf(__('%s purchased quantity', 'jigoshop-ecommerce'), '<strong>'.$totalQuantity.'</strong>'),
			'color' => $this->chartColours['item_quantity'],
			'highlight_series' => 0
        ];

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

		$query = $this->prepareQuery([
			'select' => [
				'order_item' => [
					[
						'field' => 'quantity',
						'function' => 'SUM',
						'name' => 'order_item_quantity',
                    ],
					[
						'field' => 'id',
						'function' => 'COUNT',
						'name' => 'order_item_count',
                    ],
					[
						'field' => 'cost',
						'function' => 'SUM',
						'name' => 'order_item_amount',
                    ],
                ],
				'posts' => [
					[
						'field' => 'post_date',
						'function' => '',
						'name' => 'post_date',
                    ],
                ],
            ],
			'from' => [
				'order_item' => $wpdb->prefix.'jigoshop_order_item',
            ],
			'join' => [
				'posts' => [
					'table' => $wpdb->posts,
					'on' => [
						[
							'key' => 'ID',
							'value' => 'order_item.order_id',
							'compare' => '=',
                        ]
                    ],
                ],
                'order_item_meta' => [
                    'table' => $wpdb->prefix.'jigoshop_order_item_meta',
                    'on' => [
                        [
                            'key' => 'item_id',
                            'value' => 'order_item.id',
                            'compare' => '='
                        ],
                        [
                            'key' => 'meta_key',
                            'value' => '"variation_id"',
                            'compare' => '='
                        ]
                    ]
                ]
            ],
			'where' => [
                [
                    'operator' => 'OR',
                    [
                        'key' => 'order_item.product_id',
                        'value' => sprintf('("%s")', implode('","', $this->productIds)),
                        'compare' => 'IN'
                    ],
                    [
                        'key' => 'order_item_meta.meta_value',
                        'value' => sprintf('("%s")', implode('","', $this->productIds)),
                        'compare' => 'IN'
                    ]
                ]
            ],
			'group_by' => 'order_item.order_id',
			'order_by' => 'posts.post_date ASC',
			'filter_range' => true
        ]);

		$this->reportData->orderItems = $this->getOrderReportData($query);
	}

	/**
	 * Output the report
	 */
	public function display()
	{
		/** @noinspection PhpUnusedLocalVariableInspection */
		$ranges = [
			'all' => __('All Time', 'jigoshop-ecommerce'),
			'year' => __('Year', 'jigoshop-ecommerce'),
			'last_month' => __('Last Month', 'jigoshop-ecommerce'),
			'month' => __('This Month', 'jigoshop-ecommerce'),
			'30day' => __('Last 30 Days', 'jigoshop-ecommerce'),
			'7day' => __('Last 7 Days', 'jigoshop-ecommerce'),
			'today' => __('Today', 'jigoshop-ecommerce'),
        ];

		Render::output('admin/reports/chart', [
			/** TODO This is ugly... */
			'current_tab' => Reports\SalesTab::SLUG,
			'current_type' => 'by_product',
			'ranges' => $ranges,
			'url' => remove_query_arg(['start_date', 'end_date']),
			'current_range' => $this->currentRange,
			'legends' => $this->getChartLegend(),
			'widgets' => $this->getChartWidgets(),
			'export' => $this->getExportButton(),
			'group_by' => $this->chartGroupBy
        ]);
	}

	/**
	 * [get_chart_widgets description]
	 *
	 * @return array
	 */
	public function getChartWidgets()
	{
		$widgets = [];
		$wpdb = $this->wp->getWPDB();

		$query = $this->prepareQuery([
			'select' => [
				'order_item' => [
					[
						'field' => 'id',
						'function' => 'COUNT',
						'name' => 'count',
                    ],
					[
						'field' => 'product_id',
						'function' => '',
						'name' => 'id',
                    ],
					[
						'field' => 'title',
						'function' => '',
						'name' => 'title',
                    ],
                ],
            ],
			'from' => [
				'order_item' => $wpdb->prefix.'jigoshop_order_item',
            ],
			'join' => [
				'posts' => [
					'table' => $wpdb->posts,
					'on' => [
						[
							'key' => 'ID',
							'value' => 'order_item.order_id',
							'compare' => '=',
                        ]
                    ],
                ],
            ],
			'where' => [
				[
					'key' => 'order_item.cost',
					'value' => '0',
					'compare' => '>'
                ],
            ],
			'group_by' => 'order_item.product_id',
			'order_by' => 'count DESC LIMIT 12',
			'filter_range' => true,
        ]);
		$topSellers = $this->getOrderReportData($query);

		$query = $this->prepareQuery([
			'select' => [
				'order_item' => [
					[
						'field' => 'id',
						'function' => 'COUNT',
						'name' => 'count',
                    ],
					[
						'field' => 'product_id',
						'function' => '',
						'name' => 'id',
                    ],
					[
						'field' => 'title',
						'function' => '',
						'name' => 'title',
                    ],
                ],
            ],
			'from' => [
				'order_item' => $wpdb->prefix.'jigoshop_order_item',
            ],
			'join' => [
				'posts' => [
					'table' => $wpdb->posts,
					'on' => [
						[
							'key' => 'ID',
							'value' => 'order_item.order_id',
							'compare' => '=',
                        ]
                    ],
                ],
            ],
			'where' => [
				[
					'key' => 'order_item.cost',
					'value' => '0',
					'compare' => '='
                ],
            ],
			'group_by' => 'order_item.product_id',
			'order_by' => 'count DESC LIMIT 12',
			'filter_range' => true,
        ]);
		$topFreebies = $this->getOrderReportData($query);

		$query = $this->prepareQuery([
			'select' => [
				'order_item' => [
					[
						'field' => 'cost',
						'function' => 'SUM',
						'name' => 'price',
                    ],
					[
						'field' => 'product_id',
						'function' => '',
						'name' => 'id',
                    ],
					[
						'field' => 'title',
						'function' => '',
						'name' => 'title',
                    ],
                ],
            ],
			'from' => [
				'order_item' => $wpdb->prefix.'jigoshop_order_item',
            ],
			'join' => [
				'posts' => [
					'table' => $wpdb->posts,
					'on' => [
						[
							'key' => 'ID',
							'value' => 'order_item.order_id',
							'compare' => '=',
                        ]
                    ],
                ],
            ],
			'group_by' => 'order_item.product_id',
			'order_by' => 'price DESC LIMIT 12',
			'filter_range' => true,
        ]);
		$topEarners = $this->getOrderReportData($query);

		$widgets[] = new Chart\Widget\CustomRange();
		$widgets[] = new Chart\Widget\ProductSearch($this->productIds);
		if ($topSellers) {
			$widgets[] = new Chart\Widget\TopSellers($topSellers);
		}
		if ($topFreebies) {
			$widgets[] = new Chart\Widget\TopFreebies($topFreebies);
		}
		if ($topEarners) {
			$widgets[] = new Chart\Widget\TopEarners($topEarners);
		}

		return $this->wp->applyFilters('jigoshop\admin\reports\by_product\widgets', $widgets);
	}

	public function getExportButton()
	{
		return [
			'download' => 'report-'.esc_attr($this->currentRange).'-'.date_i18n('Y-m-d', current_time('timestamp')).'.csv',
			'xaxes' => __('Date', 'jigoshop-ecommerce'),
			'groupby' => $this->chartGroupBy,
        ];
	}

	public function getMainChart()
	{
		global $wp_locale;
		// Prepare data for report
		$orderItemCounts = $this->prepareChartData($this->reportData->orderItems, 'post_date', 'order_item_count', $this->chartInterval, $this->range['start'], $this->chartGroupBy);
		$orderItemAmounts = $this->prepareChartData($this->reportData->orderItems, 'post_date', 'order_item_amount', $this->chartInterval, $this->range['start'], $this->chartGroupBy);
		$orderItemQuantity = $this->prepareChartData($this->reportData->orderItems, 'post_date', 'order_item_quantity', $this->chartInterval, $this->range['start'], $this->chartGroupBy);

		$data = [];
		$data['series'] = [];
		$data['series'][] = $this->arrayToObject([
			'label' => __('Sold quantity', 'jigoshop-ecommerce'),
			'data' => array_values($orderItemQuantity),
			'color' => $this->chartColours['item_quantity'],
			'bars' => $this->arrayToObject([
				'fillColor' => $this->chartColours['item_quantity'],
				'fill' => true,
				'show' => true,
				'lineWidth' => 0,
				'align' => 'left',
				'barWidth' => $this->barwidth * 0.4,
            ]),
			'shadowSize' => 0,
			'hoverable' => false
        ]);
		$data['series'][] = $this->arrayToObject([
			'label' => __('Number of purchases', 'jigoshop-ecommerce'),
			'data' => array_values($orderItemCounts),
			'color' => $this->chartColours['item_count'],
			'bars' => $this->arrayToObject([
				'fillColor' => $this->chartColours['item_count'],
				'fill' => true,
				'show' => true,
				'lineWidth' => 0,
				'align' => 'right',
				'barWidth' => $this->barwidth * 0.4,
            ]),
			'shadowSize' => 0,
			'hoverable' => false
        ]);
		$data['series'][] = $this->arrayToObject([
			'label' => __('Sales amount', 'jigoshop-ecommerce'),
			'data' => array_values($orderItemAmounts),
			'yaxis' => 2,
			'color' => $this->chartColours['sales_amount'],
			'points' => $this->arrayToObject([
				'show' => true,
				'radius' => 5,
				'lineWidth' => 3,
				'fillColor' => '#fff',
				'fill' => true
            ]),
			'lines' => $this->arrayToObject([
				'show' => true,
				'lineWidth' => 4,
				'fill' => false
            ]),
			'shadowSize' => 0,
			'append_tooltip' => Currency::symbol(),
        ]);
		$data['options'] = $this->arrayToObject([
			'legend' => $this->arrayToObject(['show' => false]),
			'grid' => $this->arrayToObject([
				'color' => '#aaa',
				'borderColor' => 'transparent',
				'borderWidth' => 0,
				'hoverable' => true
            ]),
            'xaxis' => $this->arrayToObject([
                'color' => '#aaa',
                'position' => 'bottom',
                'tickColor' => 'transparent',
                'mode' => 'time',
                'timeformat' => $this->chartGroupBy == 'hour' ? '%H' : ($this->chartGroupBy == 'day' ? '%d %b' : '%b'),
                'monthNames' => array_values($wp_locale->month_abbrev),
                'tickLength' => 1,
                'minTickSize' => [1, $this->chartGroupBy],
                'font' => $this->arrayToObject(['color' => '#aaa']),
            ]),
			'yaxes' => [
				$this->arrayToObject([
					'min' => 0,
					'minTickSize' => 1,
					'tickDecimals' => 0,
					'color' => '#ecf0f1',
					'font' => $this->arrayToObject(['color' => '#aaa']),
                ]),
				$this->arrayToObject([
					'position' => 'right',
					'min' => 0,
					'tickDecimals' => 2,
					'alignTicksWithAxis' => 1,
					'color' => 'transparent',
					'font' => $this->arrayToObject(['color' => '#aaa'])
                ]),
            ],
        ]);
		if ($this->chartGroupBy == 'hour') {
			$data['options']->xaxis->min = 0;
			$data['options']->xaxis->max = 24 * 60 * 60 * 1000;
		}

		return $data;
	}

	private function getChartColours()
	{
		$this->chartColours = $this->wp->applyFilters('jigoshop\admin\reports\by_product\chart_colours', [
			'sales_amount' => '#3498db',
			'item_count' => '#d4d9dc',
			'item_quantity' => '#ecf0f1'
        ]);
	}
}