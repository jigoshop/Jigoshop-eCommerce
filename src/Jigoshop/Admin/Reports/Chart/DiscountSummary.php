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
			Styles::add('jigoshop.vendors.select2', \JigoshopInit::getUrl().'/assets/css/vendors/select2.css', array('jigoshop.admin'));
			Scripts::add('jigoshop.vendors.select2', \JigoshopInit::getUrl().'/assets/js/vendors/select2.js', array('jigoshop.admin'), array('in_footer' => true));
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
		$index = 0;
        foreach($this->reportData->discounts as $type => $reportData) {
            $totalAmount = array_sum(array_map(function ($discount){
                return $discount->amount;
            }, (array)$reportData));
            $totalCount = array_sum(array_map(function ($discount){
                return $discount->count;
            }, (array)$reportData));

            $legend[] = array(
                'title' => sprintf(__('%s %s discounts in total', 'jigoshop'), '<strong>'.Product::formatPrice($totalAmount).'</strong>', Type::getName($type)),
                'color' => $this->chartColours[$index + sizeof($this->reportData->discounts)],
                'highlight_series' => $index + sizeof($this->reportData->discounts)
            );
            $legend[] = array(
                'title' => sprintf(__('%s %s discounts used in total', 'jigoshop'), '<strong>'.$totalCount.'</strong>', Type::getName($type)),
                'color' => $this->chartColours[$index],
                'highlight_series' => $index
            );
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
			'export' => $this->getExportButton(),
			'group_by' => $this->chartGroupBy
		));
	}

	/**
	 * @return array
	 */
	public function getChartWidgets()
	{
		$widgets = array();
//		$usedCoupons = $this->getUsedCoupons();
//
//		$mostDiscount = $usedCoupons;
//		$mostPopular = $usedCoupons;
//		usort($mostDiscount, function ($a, $b){
//			return $b['amount'] - $a['amount'];
//		});
//		$mostDiscount = array_slice($mostDiscount, 0, 12);
//		usort($mostPopular, function ($a, $b){
//			return $b['usage'] - $a['usage'];
//		});
//		$mostPopular = array_slice($mostPopular, 0, 12);

//		$widgets[] = new Chart\Widget\SelectCoupons($this->couponCodes, $usedCoupons);
		$widgets[] = new Chart\Widget\CustomRange();
//		if (!empty($mostPopular)) {
//			$widgets[] = new Chart\Widget\MostPopular($mostPopular);
//		}
//		if (!empty($mostDiscount)) {
//			$widgets[] = new Chart\Widget\MostDiscount($mostDiscount);
//		}

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
				'posts' => array(
					array(
						'field' => 'post_date',
						'function' => '',
						'name' => 'post_date'
					)
				),
			),
			'from' => array(
				'discount' => $wpdb->prefix . 'jigoshop_order_discount',
			),
			'join' => array(
				'posts' => array(
					'table' => $wpdb->posts,
					'on' => array(
						array(
							'key' => 'ID',
							'value' => 'discount.order_id',
							'compare' => '=',
						)
					),
				)
			),
            'filter_range' => true,
		));
		$discounts = $this->getOrderReportData($query);
		$this->reportData->discounts = [];

        foreach ($discounts as $discount) {
            if(!isset($this->reportData->discounts[$discount->type])) {
                $this->reportData->discounts[$discount->type] = [];
            }
            if(!isset($this->reportData->discounts[$discount->type][$discount->post_date])) {
                $this->reportData->discounts[$discount->type][$discount->post_date] = new \stdClass();
                $this->reportData->discounts[$discount->type][$discount->post_date]->post_date = $discount->post_date;
                $this->reportData->discounts[$discount->type][$discount->post_date]->amount = 0.0;
                $this->reportData->discounts[$discount->type][$discount->post_date]->count = 0;
                $this->reportData->discounts[$discount->type][$discount->post_date]->data = [];
            }
            $this->reportData->discounts[$discount->type][$discount->post_date]->amount += $discount->amount;
            $this->reportData->discounts[$discount->type][$discount->post_date]->count++;
            $this->reportData->discounts[$discount->type][$discount->post_date]->data[] = [
                'code' => $discount->code,
                'amount' => $discount->amount
            ];
        }
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

		$data = array();
		$data['series'] = array();
        $width = $this->barwidth / sizeof($this->reportData->discounts);
        $index = 0;
		foreach($this->reportData->discounts as $type => $reportData) {
		    $dataAmounts = $this->prepareChartData(array_filter($reportData, $filterTimes), 'post_date', 'amount', $this->chartInterval, $this->range['start'], $this->chartGroupBy);
		    $dataCounts = $this->prepareChartData(array_filter($reportData, $filterTimes), 'post_date', 'count', $this->chartInterval, $this->range['start'], $this->chartGroupBy);

            $data['series'][$index + sizeof($this->reportData->discounts)] = $this->arrayToObject(array(
                'data' => array_values($dataAmounts),
                'yaxis' => 2,
                'color' => $this->chartColours[$index + sizeof($this->reportData->discounts)],
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
            $offset = ($width * $index);
            $dataCounts = array_values($dataCounts);
            for($i = 0; $i < count($dataCounts); $i++) {
                $dataCounts[$i][0] += $offset;
            }
            $data['series'][$index] = $this->arrayToObject(array(
                'data' => $dataCounts,
                'color' => $this->chartColours[$index],
                'bars' => $this->arrayToObject(array(
                    'fillColor' => $this->chartColours[$index],
                    'fill' => true,
                    'show' => true,
                    'lineWidth' => 1,
                    'align' => 'center',
                    'barWidth' => $width * 0.8,
                    'stack' => false
                )),
                'shadowSize' => 0,
                'enable_tooltip' => true
            ));
            $index++;
		}

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
		$this->chartColours = $this->wp->applyFilters('jigoshop\admin\reports\discount_summary\chart_colours', array(
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
}