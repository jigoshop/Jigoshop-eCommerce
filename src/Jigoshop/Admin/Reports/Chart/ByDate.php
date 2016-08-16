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

class ByDate extends Chart
{
    private $chartColours = array();
    private $reportData;

    public function __construct(Wordpress $wp, Options $options, $currentRange)
    {
        parent::__construct($wp, $options, $currentRange);
        // Prepare data for report
        $this->calculateCurrentRange();
        $this->getReportData();
        $this->getChartColours();

        $wp->addAction('admin_enqueue_scripts', function () use ($wp) {
            // Weed out all admin pages except the Jigoshop Settings page hits
            if (!in_array($wp->getPageNow(), array('admin.php', 'options.php'))) {
                return;
            }

            $screen = $wp->getCurrentScreen();
            if ($screen->base != 'jigoshop_page_' . Reports::NAME) {
                return;
            }
            Styles::add('jigoshop.vendors.select2', JIGOSHOP_URL . '/assets/css/vendors/select2.css',
                array('jigoshop.admin.reports'));
            Scripts::add('jigoshop.vendors.select2', JIGOSHOP_URL . '/assets/js/vendors/select2.js',
                array('jigoshop.admin.reports'), array('in_footer' => true));
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
        $legend = array();
        switch ($this->chartGroupBy) {
            case 'hour' :
                /** @noinspection PhpUndefinedFieldInspection */
                $average_sales_title = sprintf(__('%s average sales per hour', 'jigoshop'),
                    '<strong>' . Product::formatPrice($this->reportData->averageSales) . '</strong>');
                break;
            case 'day' :
                /** @noinspection PhpUndefinedFieldInspection */
                $average_sales_title = sprintf(__('%s average daily sales', 'jigoshop'),
                    '<strong>' . Product::formatPrice($this->reportData->averageSales) . '</strong>');
                break;
            case 'month' :
            default :
                /** @noinspection PhpUndefinedFieldInspection */
                $average_sales_title = sprintf(__('%s average monthly sales', 'jigoshop'),
                    '<strong>' . Product::formatPrice($this->reportData->averageSales) . '</strong>');
                break;
        }

        /** @noinspection PhpUndefinedFieldInspection */
        $legend[] = array(
            'title' => sprintf(__('%s gross sales in this period', 'jigoshop'),
                '<strong>' . Product::formatPrice($this->reportData->totalSales) . '</strong>'),
            'tip' => __('This is the sum of the order totals including shipping and taxes.', 'jigoshop'),
            'color' => $this->chartColours['sales_amount'],
            'highlight_series' => 5
        );
        /** @noinspection PhpUndefinedFieldInspection */
        $legend[] = array(
            'title' => sprintf(__('%s net sales in this period', 'jigoshop'),
                '<strong>' . Product::formatPrice($this->reportData->netSales) . '</strong>'),
            'tip' => __('This is the sum of the order totals excluding shipping and taxes.', 'jigoshop'),
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
            'title' => sprintf(__('%s orders placed', 'jigoshop'),
                '<strong>' . $this->reportData->totalOrders . '</strong>'),
            'color' => $this->chartColours['order_count'],
            'highlight_series' => 1
        );

        /** @noinspection PhpUndefinedFieldInspection */
        $legend[] = array(
            'title' => sprintf(__('%s items purchased', 'jigoshop'),
                '<strong>' . $this->reportData->totalItems . '</strong>'),
            'color' => $this->chartColours['item_count'],
            'highlight_series' => 0
        );

        /** @noinspection PhpUndefinedFieldInspection */
        $legend[] = array(
            'title' => sprintf(__('%s charged for shipping', 'jigoshop'),
                '<strong>' . Product::formatPrice($this->reportData->totalShipping) . '</strong>'),
            'color' => $this->chartColours['shipping_amount'],
            'highlight_series' => 4
        );
        /** @noinspection PhpUndefinedFieldInspection */
        $legend[] = array(
            'title' => sprintf(__('%s worth of coupons used', 'jigoshop'),
                '<strong>' . Product::formatPrice($this->reportData->totalCoupons) . '</strong>'),
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
        $wpdb = $this->wp->getWPDB();

        $query = $this->prepareQuery(array(
            'select' => array(
                'order_report' => array(
                    array(
                        'field' => 'post_date',
                        'function' => '',
                        'name' => 'post_date',
                    ),
                    array(
                        'field' => 'count',
                        'function' => 'SUM',
                        'name' => 'count',
                    ),
                    array(
                        'field' => 'total_sales',
                        'function' => 'SUM',
                        'name' => 'total_sales',
                    ),
                    array(
                        'field' => 'total_tax',
                        'function' => 'SUM',
                        'name' => 'total_tax',
                    ),
                    /*array(
                        'field' => 'total_shipping',
                        'function' => 'SUM',
                        'name' => 'total_shipping',
                    ),*/
                    array(
                        'field' => 'discount_amount',
                        'function' => 'SUM',
                        'name' => 'discount_amount',
                    ),
                    array(
                        'field' => 'order_item_count',
                        'function' => 'SUM',
                        'name' => 'order_item_count',
                    ),
                ),
            ),
            'from' => array(
                'order_report' => '(' . $this->prepareQuery(array(
                        'select' => array(
                            'posts' => array(
                                array(
                                    'field' => 'post_date',
                                    'function' => '',
                                    'name' => 'post_date',
                                ),
                                array(
                                    'field' => 'ID',
                                    'function' => 'COUNT',
                                    'name' => 'count',
                                    'distinct' => true
                                )
                            ),
                            'total_sales' => array(
                                array(
                                    'field' => 'meta_value',
                                    'function' => 'SUM',
                                    'name' => 'total_sales',
                                    'distinct' => true
                                )
                            ),
                            /*
                             'total_shipping' => array(
                                 array(
                                     'field' => 'DISTINCT meta_value',
                                     'function' => 'SUM',
                                     'name' => 'total_shipping',
                                    'distinct' => true
                                 )
                             ),*/
                            'discount_amount' => array(
                                array(
                                    'field' => 'meta_value',
                                    'function' => 'SUM',
                                    'name' => 'discount_amount',
                                    'distinct' => true
                                )
                            ,
                            ),
                            'order_item' => array(
                                array(
                                    'field' => 'quantity',
                                    'function' => 'SUM',
                                    'name' => 'order_item_count',
                                ),
                                array(
                                    'field' => 'tax',
                                    'function' => 'SUM',
                                    'name' => 'total_tax',
                                )
                            ),
                            'total_tax' => array(),
                        ),
                        'from' => array(
                            'posts' => $wpdb->posts,
                        ),
                        'join' => array(
                            'total_sales' => array(
                                'table' => $wpdb->postmeta,
                                'on' => array(
                                    array(
                                        'key' => 'post_id',
                                        'value' => 'posts.ID',
                                        'compare' => '=',
                                    ),
                                    array(
                                        'key' => 'meta_key',
                                        'value' => '"total"',
                                        'compare' => '=',
                                    ),
                                ),
                            ),
                            /*'total_shipping' => array(
                                'table' => $wpdb->postmeta,
                                'on' => array(
                                    array(
                                        'key' => 'post_id',
                                        'value' => 'posts.ID',
                                        'compare' => '=',
                                    ),
                                    array(
                                        'key' => 'meta_key',
                                        'value' => '"shipping"',
                                        'compare' => '=',
                                    ),
                                ),
                            ),*/
                            'discount_amount' => array(
                                'table' => $wpdb->postmeta,
                                'on' => array(
                                    array(
                                        'key' => 'post_id',
                                        'value' => 'posts.ID',
                                        'compare' => '=',
                                    ),
                                    array(
                                        'key' => 'meta_key',
                                        'value' => '"discount"',
                                        'compare' => '=',
                                    ),
                                ),
                            ),
                            'order_item' => array(
                                'table' => $wpdb->prefix . 'jigoshop_order_item',
                                'on' => array(
                                    array(
                                        'key' => 'order_id',
                                        'value' => 'posts.ID',
                                        'compare' => '=',
                                    ),
                                ),
                            ),
                        ),
                        'where' => array(
                            array(
                                'key' => 'posts.post_type',
                                'value' => '"shop_order"',
                                'compare' => '='
                            ),
                            array(
                                'key' => 'posts.post_status',
                                'value' => sprintf('("%s")', implode('","', $this->orderStatus)),
                                'compare' => 'IN',
                            )
                        ),
                        'group_by' => 'posts.ID',
                        'filter_range' => true,
                    )) . ')',
            ),
            'group_by' => str_replace('posts', 'order_report', $this->groupByQuery),
            'order_by' => 'order_report.post_date ASC'
        ));

        $this->reportData->orders = $this->getOrderReportData($query);
        $this->reportData->totalSales = array_sum(wp_list_pluck($this->reportData->orders, 'total_sales'));
        $this->reportData->totalTax = array_sum(wp_list_pluck($this->reportData->orders, 'total_tax'));
        $this->reportData->totalShipping = 0;//array_sum(wp_list_pluck($this->reportData->orders, 'total_shipping'));
        $this->reportData->totalShippingTax = 0;//array_sum(wp_list_pluck($this->reportData->orders, 'total_shipping_tax'));
        $this->reportData->totalCoupons = array_sum(wp_list_pluck($this->reportData->orders, 'discount_amount'));
        $this->reportData->totalOrders = absint(array_sum(wp_list_pluck($this->reportData->orders, 'count')));
        $this->reportData->totalItems = absint(array_sum(wp_list_pluck($this->reportData->orders, 'order_item_count')));
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

        Render::output('admin/reports/chart', array(
            /** TODO This is ugly... */
            'current_tab' => Reports\SalesTab::SLUG,
            'current_type' => 'by_date',
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
     * [get_chart_widgets description]
     *
     * @return array
     */
    public function getChartWidgets()
    {
        $widgets = array();

        $widgets[] = new Chart\Widget\CustomRange();
        $widgets[] = new Chart\Widget\OrderStatusFilter($this->orderStatus);

        return $this->wp->applyFilters('jigoshop\admin\reports\by_date\widgets', $widgets);
    }

    /**
     * Output an export link
     */
    public function getExportButton()
    {
        return array(
            'download' => 'report-' . esc_attr($this->currentRange) . '-' . date_i18n('Y-m-d',
                    current_time('timestamp')) . '.csv',
            'xaxes' => __('Date', 'jigoshop'),
            'groupby' => $this->chartGroupBy,
        );
    }

    /**
     * Get the main chart
     *
     * @return string
     */
    public function getMainChart()
    {
        // TODO: Remove this...
        global $wp_locale;

        $orderCounts = $this->prepareChartData($this->reportData->orders, 'post_date', 'count', $this->chartInterval,
            $this->range['start'], $this->chartGroupBy);
        $orderItemCounts = $this->prepareChartData($this->reportData->orders, 'post_date', 'order_item_count',
            $this->chartInterval, $this->range['start'], $this->chartGroupBy);
        $orderAmounts = $this->prepareChartData($this->reportData->orders, 'post_date', 'total_sales',
            $this->chartInterval, $this->range['start'], $this->chartGroupBy);
        $couponAmounts = $this->prepareChartData($this->reportData->orders, 'post_date', 'discount_amount',
            $this->chartInterval, $this->range['start'], $this->chartGroupBy);
        //$shippingAmounts = $this->prepareChartData($this->reportData->orders, 'post_date', 'total_shipping', $this->chartInterval, $this->range['start'], $this->chartGroupBy);
        //$shippingTaxAmounts = $this->prepareChartData($this->reportData->orders, 'post_date', 'total_shipping_tax', $this->chartInterval, $this->range['start'], $this->chartGroupBy);
        $shippingAmounts = array();
        $shippingTaxAmounts = array();
        $taxAmounts = $this->prepareChartData($this->reportData->orders, 'post_date', 'total_tax', $this->chartInterval,
            $this->range['start'], $this->chartGroupBy);

        $netOrderAmounts = array();

        foreach ($orderAmounts as $orderAmountKey => $orderAmountValue) {
            $netOrderAmounts[$orderAmountKey] = $orderAmountValue;
            $netOrderAmounts[$orderAmountKey][1] = $netOrderAmounts[$orderAmountKey][1] /*- $shippingAmounts[$orderAmountKey][1] - $shippingTaxAmounts[$orderAmountKey][1]*/ - $taxAmounts[$orderAmountKey][1];
        }

        $data = array();
        $data['series'] = array();
        $data['series'][] = $this->arrayToObject(array(
            'label' => esc_js(__('Number of items sold', 'jigoshop')),
            'data' => array_values($orderItemCounts),
            'color' => $this->chartColours['item_count'],
            'bars' => array(
                'fillColor' => $this->chartColours['item_count'],
                'fill' => true,
                'show' => true,
                'lineWidth' => 0,
                'align' => 'left',
                'barWidth' => $this->barwidth * 0.2,
            ),
            'shadowSize' => 0,
            'hoverable' => false
        ));
        $data['series'][] = $this->arrayToObject(array(
            'label' => esc_js(__('Number of orders', 'jigoshop')),
            'data' => array_values($orderCounts),
            'color' => $this->chartColours['order_count'],
            'bars' => array(
                'fillColor' => $this->chartColours['order_count'],
                'fill' => true,
                'show' => true,
                'lineWidth' => 0,
                'align' => 'right',
                'barWidth' => $this->barwidth * 0.2,
            ),
            'shadowSize' => 0,
            'hoverable' => false
        ));
               $data['series'][] = $this->arrayToObject(array(
                   'label' => esc_js(__('Average sales amount', 'jigoshop')),
                   'data' => array(
                       array(min(array_keys($orderAmounts)), $this->reportData->averageSales),
                       array(max(array_keys($orderAmounts)), $this->reportData->averageSales),
                   ),
                   'yaxis' => 2,
                   'color' => $this->chartColours['average'],
                   'points' => $this->arrayToObject(array('show' => false)),
                   'lines' => $this->arrayToObject(array(
                       'show' => true,
                       'lineWidth' => 2,
                       'fill' => false
                   )),
                   'shadowSize' => 0,
                   'hoverable' => false
               ));
               $data['series'][] = $this->arrayToObject(array(
                   'label' => esc_js(__('Coupon amount', 'jigoshop')),
                   'data' => array_map(array($this, 'roundChartTotals'), array_values($couponAmounts)),
                   'yaxis' => 2,
                   'color' => $this->chartColours['coupon_amount'],
                   'points' => $this->arrayToObject(array(
                       'show' => true,
                       'radius' => 5,
                       'lineWidth' => 2,
                       'fillColor' => '#fff',
                       'fill' => true
                   )),
                   'lines' => $this->arrayToObject(array(
                       'show' => true,
                       'lineWidth' => 2,
                       'fill' => false
                   )),
                   'shadowSize' => 0,
                   'append_tooltip' => Currency::symbol(),
               ));
               $data['series'][] = $this->arrayToObject(array(
                   'label' => esc_js(__('Shipping amount', 'jigoshop')),
                   'data' => array_map(array($this, 'roundChartTotals'), array_values($shippingAmounts)),
                   'yaxis' => 2,
                   'color' => $this->chartColours['shipping_amount'],
                   'points' => $this->arrayToObject(array(
                       'show' => true,
                       'radius' => 5,
                       'lineWidth' => 2,
                       'fillColor' => '#fff',
                       'fill' => true
                   )),
                   'lines' => $this->arrayToObject(array(
                       'show' => true,
                       'lineWidth' => 2,
                       'fill' => false
                   )),
                   'shadowSize' => 0,
                   'append_tooltip' => Currency::symbol(),
               ));
               $data['series'][] = $this->arrayToObject(array(
                   'label' => esc_js(__('Gross Sales amount', 'jigoshop')),
                   'data' => array_map(array($this, 'roundChartTotals'), array_values($orderAmounts)),
                   'yaxis' => 2,
                   'color' => $this->chartColours['sales_amount'],
                   'points' => $this->arrayToObject(array(
                       'show' => true,
                       'radius' => 5,
                       'lineWidth' => 2,
                       'fillColor' => '#fff',
                       'fill' => true
                   )),
                   'lines' => $this->arrayToObject(array(
                       'show' => true,
                       'lineWidth' => 2,
                       'fill' => false
                   )),
                   'shadowSize' => 0,
                   'append_tooltip' => Currency::symbol(),
               ));
               $data['series'][] = $this->arrayToObject(array(
                   'label' => esc_js(__('Net Sales amount', 'jigoshop')),
                   'data' => array_map(array($this, 'roundChartTotals'), array_values($netOrderAmounts)),
                   'yaxis' => 2,
                   'color' => $this->chartColours['net_sales_amount'],
                   'points' => $this->arrayToObject(array(
                       'show' => true,
                       'radius' => 6,
                       'lineWidth' => 4,
                       'fillColor' => '#fff',
                       'fill' => true
                   )),
                   'lines' => $this->arrayToObject(array(
                       'show' => true,
                       'lineWidth' => 5,
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
                'hoverable' => true,
            )),
            'xaxis' => $this->arrayToObject(array(
                'color' => '#aaa',
                'position' => 'bottom',
                'tickColor' => 'transparent',
                'mode' => 'time',
                'timeformat' => $this->chartGroupBy == 'hour' ? '%H' : ($this->chartGroupBy == 'day' ? '%d %b' : '%b'),
                'monthNames' => array_values($wp_locale->month_abbrev),
                'tickLength' => 1,
                'minTickSize' => array(1, $this->chartGroupBy),
                'font' => $this->arrayToObject(array('color' => '#aaa')),
            )),
            'yaxes' => array(
                $this->arrayToObject(array(
                    'min' => 0,
                    'minTickSize' => 1,
                    'color' => '#d4d9dc',
                    'font' => $this->arrayToObject(array('color' => '#aaa')),
                )),
                $this->arrayToObject(array(
                    'position' => 'right',
                    'min' => 0,
                    'tickDecimals' => 2,
                    'alignTicksWithAxis' => 1,
                    'autoscaleMargin' => 0,
                    'color' => 'transparent',
                    'font' => $this->arrayToObject(array('color' => '#aaa'))
                )),
            ),
        ));
        if ($this->chartGroupBy == 'hour') {
            $data['options']->xaxis->min = 0;
            $data['options']->xaxis->max = 24 * 60 * 60 * 1000;
        }

        return $data;
    }

    /**
     * Round our totals correctly
     *
     * @param  string $amount
     *
     * @return string
     */
    private function roundChartTotals($amount)
    {
        if (is_array($amount)) {
            return array_map(array($this, 'roundChartTotals'), $amount);
        } else {
            if (is_float($amount) || $amount == 0) {
                return number_format($amount, 2, '.', '');
            } else {
                return $amount;
            }
        }
    }

    private function getChartColours()
    {
        $this->chartColours = $this->wp->applyFilters('jigoshop\admin\reports\by_date\chart_colours', array(
            'sales_amount' => '#b1d4ea',
            'net_sales_amount' => '#3498db',
            'average' => '#95a5a6',
            'order_count' => '#dbe1e3',
            'item_count' => '#ecf0f1',
            'shipping_amount' => '#5cc488',
            'coupon_amount' => '#f1c40f',
        ));
    }
}