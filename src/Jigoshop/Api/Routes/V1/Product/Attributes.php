<?php

namespace Jigoshop\Api\Routes\V1\Product;

use Jigoshop\Admin\Migration\Exception;
use Jigoshop\Api\Contracts\ApiControllerContract;
use Jigoshop\Api\Routes\V1\BaseController;
use Jigoshop\Entity\Product as ProductEntity;
use Jigoshop\Service\ProductService;
use Slim\App;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Class Products
 * @package Jigoshop\Api\Routes\V1;
 * @author Krzysztof Kasowski
 */
class Attributes extends BaseController implements ApiControllerContract
{
    /** @var  App */
    protected $app;
    /** @var  $product */
    protected $product;

    protected $serviceName = 'jigoshop.service.product';
    protected $entityName = 'attribute';

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
    }


    public function findAll(Request $request, Response $response, $args)
    {
        $queryParams = $this->setDefaultQueryParams($request->getParams());

        $this->setProduct($args);
        $items = $this->getObjects($args);
        $itemsCount = $this->getObjectsCount();
        return $response->withJson([
            'success' => true,
            'all_results' => $itemsCount,
            'pagelen' => $queryParams['pagelen'],
            'page' => $queryParams['page'],
            'next' => '',
            'previous' => '',
            'data' => array_values($items),
        ]);
    }

    public function findOne(Request $request, Response $response, $args)
    {
        $this->setProduct($args);
        $attribute = $this->validateObjectFinding($args);
        return $response->withJson([
            'success' => true,
            'data' => $attribute,
        ]);
    }


    /**
     * @param $args
     */
    protected function setProduct($args)
    {
        // validating product first
        if (!isset($args['productId']) || empty($args['productId'])) {
            throw new Exception("Product Id was not provided");
        }
        $product = $this->service->find($args['productId']);
        if (!$product instanceof ProductEntity) {
            throw new Exception("Product not found.", 404);
        }
        $this->product = $product;
    }


    public function delete(Request $request, Response $response, $args)
    {
        // TODO: Implement delete() method.
    }

    protected function getObjects(array $args)
    {
        /** @var ProductService $service */
        $service = $this->service;
        return $service->getAttributes($this->product->getId());
    }

    protected function getObjectsCount()
    {
        /** @var ProductService $service */
        $service = $this->service;
        $items = $service->getAttributes($this->product->getId());
        return count($items);
    }

    protected function validateObjectFinding($args)
    {
        if (!isset($args['id']) || empty($args['id'])) {
            throw new Exception("$this->entityName ID was not provided");
        }

        $object = $this->service->getAttribute($args['id']);
        $entity = self::JIGOSHOP_ENTITY_PREFIX . 'Product\\'. ucfirst($this->entityName);

        if (!$object instanceof $entity) {
            throw new Exception("$this->entityName not found.", 404);
        }

        return $object;
    }

}