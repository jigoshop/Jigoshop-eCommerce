<?php

namespace Jigoshop\Api\Routes\V1;

use Jigoshop\Entity\Product as ProductEntity;
use Slim\App;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Class Products
 * @package Jigoshop\Api\Routes\V1;
 * @author Krzysztof Kasowski
 */
class Products extends PostController
{
    /** @var  App */
    protected $app;

    /**
     * Products constructor.
     * @param App $app
     */
    public function __construct(App $app)
    {
        parent::__construct($app);
        $this->app = $app;

        $app->get('', array($this, 'findAll'));
        $app->get('/{id:[0-9]+}', array($this, 'findOne'));
        $app->post('', array($this, 'create'));
    }

    /**
     * overrided create function from PostController
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return Response
     */
    public function create(Request $request, Response $response, $args)
    {
        $factory = $this->app->getContainer()->di->get('jigoshop.factory.product');
        self::overridePostProductData();
        $product = $factory->create(null);
        $service = $this->app->getContainer()->di->get('jigoshop.service.product');
        $service->save($product);

        return $response->withJson([
            'success' => true,
            'data' => "$this->entityName successfully created",
        ]);
    }

    /**
     * helper function that makes product saving available
     */
    public static function overridePostProductData()
    {
        foreach ($_POST['jigoshop_product'] as $key => $item) {
            $_POST['product'][$key] = $item;
        }
        unset($_POST['jigoshop_product']);
    }

}