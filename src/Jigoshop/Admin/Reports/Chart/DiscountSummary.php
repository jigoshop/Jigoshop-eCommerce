<?php

namespace Jigoshop\Admin\Reports\Chart;

use Jigoshop\Admin\Reports;
use Jigoshop\Admin\Reports\Chart;
use Jigoshop\Core\Options;
use Jigoshop\Entity\Order\Discount\Type;
use Jigoshop\Helper\Currency;
use Jigoshop\Helper\Product;
use Jigoshop\Helper\Render;
use Jigoshop\Helper\Scripts;
use Jigoshop\Helper\Styles;
use WPAL\Wordpress;

class DiscountSummary extends Chart
{
	/** @var array */
	public $chartColours = [];
	/** @var array */
	public $codes = [];
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

		if(isset($_GET['codes'])) {
		    $this->codes = $_GET['codes'];
		    foreach($this->codes as $type => $value) {
		        if(is_array($value)) {
		            $this->codes[$type] = array_filter(array_map('sanitize_text_field', $value));
                } else {
                    $this->codes[$type] = array_filter([sanitize_text_field($value)]);
                }
            }
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
			Scripts::localize('jigoshop.reports.chart', 'chart_data', $this->getMainChart());
		});
	}

	/**
	 * @return array
	 */
	public function getChartLegend()
	{
		$legend = [];

		$this->getReportData();
		$index = 0;
        foreach($this->reportData->discounts as $type => $reportData) {
            $totalAmount = array_sum(array_map(function ($discount){
                return $discount->amount;
            }, (array)$reportData));
            $totalCount = array_sum(array_map(function ($discount){
                return $discount->count;
            }, (array)$reportData));

            $legend[] = [
                'title' => sprintf(__('%s %s discounts in total', 'jigoshop-ecommerce'), '<strong>'.Product::formatPrice($totalAmount).'</strong>', Type::getName($type)),
                'color' => $this->chartColours[$index + sizeof($this->reportData->discounts)],
                'highlight_series' => $index + sizeof($this->reportData->discounts)
            ];
            $legend[] = [
                'title' => sprintf(__('%s %s discounts used in total', 'jigoshop-ecommerce'), '<strong>'.$totalCount.'</strong>', Type::getName($type)),
                'color' => $this->chartColours[$index],
                'highlight_series' => $index
            ];
            $index++;
        }

		return $legend;
	}

	/**
	 *
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
			'current_type' => 'discount_summary',
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
	 * @return array
	 */
	public function getChartWidgets()
	{
		$widgets = [];
		$wpdb = $this->wp->getWPDB();
		$query = $this->prepareQuery([
            'select' => [
                'discount' => [
                    [
                        'field' => 'type',
                        'function' => '',
                        'name' => 'type',
                    ],
                    [
                        'field' => 'code',
                        'function' => '',
                        'name' => 'code',
                    ],
                    [
                        'field' => 'amount',
                        'function' => '',
                        'name' => 'amount',
                    ]
                ],
                'posts' => [
                    [
                        'field' => 'post_date',
                        'function' => '',
                        'name' => 'post_date'
                    ]
                ],
            ],
            'from' => [
                'discount' => $wpdb->prefix . 'jigoshop_order_discount',
            ],
            'join' => [
                'posts' => [
                    'table' => $wpdb->posts,
                    'on' => [
                        [
                            'key' => 'ID',
                            'value' => 'discount.order_id',
                            'compare' => '=',
                        ]
                    ],
                ]
            ],
            'filter_range' => true,
        ]);
		$discounts = $this->getOrderReportData($query);
		$reportData = $this->parseOrderReportData($discounts);

        $widgets[] = new Chart\Widget\CustomRange();

		foreach ($reportData as $type => $data) {

		    if($type == Type::USER_DEFINED) {
		        continue;
            }
            $mostDiscount = $mostPopular = $codes = $this->getUsedCodes($data);
		    $codes = array_keys($codes);
            usort($mostDiscount, function ($a, $b){
                return $b['amount'] - $a['amount'];
            });
            $mostDiscount = array_slice($mostDiscount, 0, 12);
            usort($mostPopular, function ($a, $b){
                return $b['count'] - $a['count'];
            });
            $mostPopular = array_slice($mostPopular, 0, 12);
            $widgets[] = new Chart\Widget\SelectCodes($type,  isset($this->codes[$type]) ? $this->codes[$type] : [], $codes);
            if (!empty($mostPopular)) {
                $widgets[] = new Chart\Widget\MostPopular($type, $mostPopular);
            }
            if (!empty($mostDiscount)) {
                $widgets[] = new Chart\Widget\MostDiscount($type, $mostDiscount);
            }
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

		$args = [
            'select' => [
                'discount' => [
                    [
                        'field' => 'type',
                        'function' => '',
                        'name' => 'type',
                    ],
                    [
                        'field' => 'code',
                        'function' => '',
                        'name' => 'code',
                    ],
                    [
                        'field' => 'amount',
                        'function' => '',
                        'name' => 'amount',
                    ]
                ],
                'posts' => [
                    [
                        'field' => 'post_date',
                        'function' => '',
                        'name' => 'post_date'
                    ]
                ],
            ],
            'from' => [
                'discount' => $wpdb->prefix . 'jigoshop_order_discount',
            ],
            'join' => [
                'posts' => [
                    'table' => $wpdb->posts,
                    'on' => [
                        [
                            'key' => 'ID',
                            'value' => 'discount.order_id',
                            'compare' => '=',
                        ]
                    ],
                ]
            ],
            'filter_range' => true,
        ];
		if(count($this->codes)) {
		    $args['where'] = [];
            $args['where']['operator'] = 'OR';
		    foreach($this->codes as $type => $codes) {
                $args['where'][] = [
                    [
                        'key' => 'discount.type',
                        'value' => '"'.$type.'"',
                        'compare' => '='
                    ],
                    [
                        'key' => 'discount.code',
                        'value' => sprintf('("%s")', implode('","', $codes)),
                        'compare' => 'IN',
                    ]
                ];
            }
        }
        $query = $this->prepareQuery($args);
        
        $discounts = $this->getOrderReportData($query);
		$this->reportData->discounts = $this->parseOrderReportData($discounts);
    }
    
    private function parseOrderReportData($data)
    {
        $discounts = [];
        foreach ($data as $discount) {
            if(!isset($discounts[$discount->type])) {
                $discounts[$discount->type] = [];
            }
            if(!isset($discounts[$discount->type][$discount->post_date])) {
                $discounts[$discount->type][$discount->post_date] = new \stdClass();
                $discounts[$discount->type][$discount->post_date]->post_date = $discount->post_date;
                $discounts[$discount->type][$discount->post_date]->amount = 0.0;
                $discounts[$discount->type][$discount->post_date]->count = 0;
                $discounts[$discount->type][$discount->post_date]->data = [];
            }
            $discounts[$discount->type][$discount->post_date]->amount += $discount->amount;
            $discounts[$discount->type][$discount->post_date]->count++;
            $discounts[$discount->type][$discount->post_date]->data[] = [
                'code' => $discount->code,
                'amount' => $discount->amount
            ];
        }

        return $discounts;
    }

	/**
	 * @return array
	 */
	public function getExportButton()
	{
		return [
			'download' => 'report-'.esc_attr($this->currentRange).'-'.date_i18n('Y-m-d', current_time('timestamp')).'.csv',
			'xaxes' => __('Date', 'jigoshop-ecommerce'),
			'groupby' => $this->chartGroupBy,
        ];
	}

	/**
	 * @return array
	 */
	public function getMainChart()
	{
		global $wp_locale;

		$data = [];
		$data['series'] = [];
        $index = 0;
		foreach($this->reportData->discounts as $type => $reportData) {
            $width = $this->barwidth / count($this->reportData->discounts);
		    $dataAmounts = $this->prepareChartData($reportData, 'post_date', 'amount', $this->chartInterval, $this->range['start'], $this->chartGroupBy);
		    $dataCounts = $this->prepareChartData($reportData, 'post_date', 'count', $this->chartInterval, $this->range['start'], $this->chartGroupBy);

            $data['series'][$index + sizeof($this->reportData->discounts)] = $this->arrayToObject([
                'label' => sprintf(__('%s discounts in total', 'jigoshop-ecommerce'), Type::getName($type)),
                'data' => array_values($dataAmounts),
                'yaxis' => 2,
                'color' => $this->chartColours[$index + sizeof($this->reportData->discounts)],
                'points' => $this->arrayToObject([
                    'show' => true,
                    'radius' => 5,
                    'lineWidth' => 4,
                    'fillColor' => '#fff',
                    'fill' => true,
                ]),
                'lines' => $this->arrayToObject([
                    'show' => true,
                    'lineWidth' => 4,
                    'fill' => false,
                ]),
                'shadowSize' => 0,
                'append_tooltip' => Currency::symbol(),
            ]);
            $offset = ($width * $index);
            $dataCounts = array_values($dataCounts);
            for($i = 0; $i < count($dataCounts); $i++) {
                $dataCounts[$i][0] += $offset;
            }
            $data['series'][$index] = $this->arrayToObject([
                'label' => sprintf(__('%s discounts used in total', 'jigoshop-ecommerce'), Type::getName($type)),
                'data' => $dataCounts,
                'color' => $this->chartColours[$index],
                'bars' => $this->arrayToObject([
                    'fillColor' => $this->chartColours[$index],
                    'fill' => true,
                    'show' => true,
                    'lineWidth' => 1,
                    'align' => 'center',
                    'barWidth' => $width * 0.8,
                    'stack' => false
                ]),
                'shadowSize' => 0,
                'enable_tooltip' => true
            ]);
            $index++;
		}
		if(empty($data['series'])) {
            $dummy = $this->prepareChartData([], '', '', $this->chartInterval, $this->range['start'], $this->chartGroupBy);
            $data['series'][] = $this->arrayToObject([
                'label' => __('No discounts for selected period.', 'jigoshop-ecommerce'),
                'data' => array_values($dummy),
                'yaxis' => 2,
                'color' => $this->chartColours[0],
                'points' => $this->arrayToObject([
                    'show' => true,
                    'radius' => 5,
                    'lineWidth' => 4,
                    'fillColor' => '#fff',
                    'fill' => true,
                ]),
                'lines' => $this->arrayToObject([
                    'show' => true,
                    'lineWidth' => 4,
                    'fill' => false,
                ]),
                'shadowSize' => 0,
                'append_tooltip' => Currency::symbol(),
            ]);
        }

		$data['options'] = $this->arrayToObject([
			'legend' => $this->arrayToObject(['show' => false,]),
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
					'font' => $this->arrayToObject(['color' => '#aaa'])
                ]),
				$this->arrayToObject([
					'position' => 'right',
					'min' => 0,
					'tickDecimals' => 2,
					'alignTicksWithAxis' => 1,
					'autoscaleMargin' => 0,
					'color' => 'transparent',
					'font' => $this->arrayToObject(['color' => '#aaa'])
                ])
            ],
        ]);
		if ($this->chartGroupBy == 'hour') {
			$data['options']->xaxis->min = 0;
			$data['options']->xaxis->max = 24 * 60 * 60 * 1000;
		}

		return $data;
	}

	/**
	 *
	 */
	private function getChartColours()
	{
		$this->chartColours = $this->wp->applyFilters('jigoshop\admin\reports\discount_summary\chart_colours', [
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
        ]);
	}

	private function getUsedCodes($reportData)
    {
        $codes = [];
        foreach ($reportData as $singleData) {
            foreach ($singleData->data as $discount) {
                if(!isset($codes[$discount['code']])) {
                    $codes[$discount['code']] = [
                        'code' => $discount['code'],
                        'amount' => 0.0,
                        'count' => 0,
                    ];
                }

                $codes[$discount['code']]['amount'] += $discount['amount'];
                $codes[$discount['code']]['count']++;
            }
        }

        return $codes;
    }
}