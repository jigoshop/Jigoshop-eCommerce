<?php

namespace Jigoshop\Api\Routes\V1;

use Jigoshop\Admin\Reports\Chart;
use Jigoshop\Admin\Reports\SalesTab;

use Jigoshop\Exception;
use Slim\App;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Class Reports
 * @package Jigoshop\Api\Routes\V1;
 * @author Krzysztof Kasowski
 */
class Reports
{
    /** @var  App */
    private $app;

    /**
     * @apiDefine ReportsReturnObject
     * @apiSuccess {Object} order Orders objects returned in report
     * @apiSuccess {Datetime} order.post_date Datetime when order was requested
     * @apiSuccess {Number} order.count Count value
     * @apiSuccess {Number} order.total_sales Number of total sales
     * @apiSuccess {Number} order.total_tax Total tax value
     * @apiSuccess {Number} order.discount_amout Discount value in this order
     * @apiSuccess {Number} order.order_item_count Number of items ordered
     * @apiSuccess {Number} order.total_shipping Total value of shipping
     * @apiSuccess {Number} order.total_shipping_tax Total value of shipping tax
     * @apiSuccess {Number} order.totalSales Number of all sales.
     * @apiSuccess {Number} order.totalTax Total value of Tax
     * @apiSuccess {Number} order.totalShipping Total value of shipping
     * @apiSuccess {Number} order.totalShipping Total value of shipping
     * @apiSuccess {Number} order.totalShippingTax Total value of shipping tax
     * @apiSuccess {Number} totalCoupons Total number of coupons used
     * @apiSuccess {Number} totalOrders Total number of orders
     * @apiSuccess {Number} totalItems Total item ordered
     * @apiSuccess {Number} averageSales Average sale value
     * @apiSuccess {Number} netSales Number of net sales
     */

    /**
     * Orders constructor.
     * @param App $app
     */
    public function __construct(App $app)
    {
        $this->app = $app;

        /**
         * @api {get} /reports Request Report information
         * @apiName GetReport
         * @apiGroup Report
         *
         * @apiUse ReportsReturnObject
         * @apiError NotFound Invalid report type.
         * @apiPermission read_reports
         */
        $app->get('', [$this, 'getReports']);
    }

    public function getReports(Request $request, Response $response, $args)
    {
        /** @var SalesTab $sales */
        $sales = $this->app->getContainer()->di->get('jigoshop.admin.reports.sales');
        $chart = $sales->getChart();
        if ($chart instanceof Chart) {
            return $response->withJson($chart->getReportData());
        } else {
            throw new Exception('Invalid report type.', 404);
        }
    }
}