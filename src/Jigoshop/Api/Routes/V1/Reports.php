<?php

namespace Jigoshop\Api\Routes\V1;

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

    }
}