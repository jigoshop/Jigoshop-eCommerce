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
     * Orders constructor.
     * @param App $app
     */
    public function __construct(App $app)
    {
        $this->app = $app;
        $app->get('', array($this, 'getReports'));
    }

    public function getReports(Request $request, Response $response, $args)
    {
        /** @var SalesTab $sales */
        $sales = $this->app->getContainer()->di->get('jigoshop.admin.reports.sales');
        $chart = $sales->getChart();
        if($chart instanceof Chart) {
            return $response->withJson($chart->getReportData());
        } else {
            throw new Exception('Invalid report type.', 404);
        }
    }
}