<?php

namespace Jigoshop\Api\Routes\V1;

use Jigoshop\Core\Types;
use Jigoshop\Service\ProductService;
use Slim\App;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Class Products
 * @package Jigoshop\Api\Routes\V1;
 * @author Krzysztof Kasowski
 */
class Products
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
        $app->get('', array($this, 'getProducts'));
        $app->get('/{id:[0-9]+}', array($this, 'getProduct'));
    }

    public function getProducts(Request $request, Response $response, $args)
    {
        /** @var ProductService $service */
        $service = $this->app->getContainer()->di->get('jigoshop.service.product');

        $queryParams = $request->getParams();
        $queryParams['pagelen'] = isset($queryParams['pagelen']) && is_numeric($queryParams['pagelen']) ? (int)$queryParams['pagelen'] : 10;
        $queryParams['page'] = isset($queryParams['page']) && is_numeric($queryParams['page']) ? (int)$queryParams['page'] : 1;
        $allProducts = $service->getProductsCount();

        $products = $service->findByQuery(new \WP_Query([
            'post_type' => Types::PRODUCT,
            'posts_per_page' => $queryParams['pagelen'],
            'paged' => $queryParams['page'],
        ]));

        return $response->withJson([
            'success' => true,
            'all_results' => $allProducts,
            'pagelen' => 10,
            'page' => 1,
            'next' => '',
            'previous' =>  '',
            'data' => [],
        ]);
    }

    public function getProduct(Request $request, Response $response, $args)
    {
        $service = $this->app->getContainer()->di->get('jigoshop.service.product');
    }
}