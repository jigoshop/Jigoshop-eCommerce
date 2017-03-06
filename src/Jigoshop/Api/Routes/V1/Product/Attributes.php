<?php

namespace Jigoshop\Api\Routes\V1\Product;

use Jigoshop\Admin\Migration\Exception;
use Jigoshop\Api\Contracts\ApiControllerContract;
use Jigoshop\Api\Routes\V1\BaseController;
use Jigoshop\Entity\Product as ProductEntity;
use Jigoshop\Entity\Product;
use Jigoshop\Entity\Product\Attribute as AttributeEntity;
use Jigoshop\Entity\Product\Attribute;
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
    /** @var Product $product */
    protected $product;

    /**
     * product service is service we are using for product attributes
     * @var string
     */
    protected $serviceName = 'jigoshop.service.product';
    /**
     * @var string
     */
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
        $app->put('/{id:[0-9]+}', array($this, 'update'));
        $app->get('', array($this, 'create'));
    }

    /**
     * get all attributes for product
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return Response
     */
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

    /**
     * get all attributes for product
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return Response
     */
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
     * overrided create function from BaseController
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return Response
     */
    public function create(Request $request, Response $response, $args)
    {
        $this->setProduct($args);

        $attribute = new AttributeEntity\Custom();
        $label = trim(strip_tags($_POST['attribute_label']));

        if (empty($label)) {
            throw new Exception(__('Custom attribute requires label to be set.', 'jigoshop'));
        }

        $attribute->setLabel($label);
        $attribute->setSlug($this->wp->getHelpers()->sanitizeTitle($label));
        $this->service->saveAttribute($attribute);
        $attributeExists = false;

        if ($attribute === null) {
            throw new Exception(__('Attribute does not exists.', 'jigoshop'));
        }

        $this->populateAttribute($attribute, $attributeExists, $_POST);

        $this->addAndSaveAttribute($attribute);

        return $response->withJson([
            'success' => true,
            'data' => "Attribute successfully created",
        ]);
    }

    /**
     * overrided update function from BaseController
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return Response
     */
    public function update(Request $request, Response $response, $args)
    {
        $this->setProduct($args);
        /** @var ProductEntity\Attribute $attribute */
        $attribute = $this->validateObjectFinding($args);

        $data = $request->getParsedBody();
        $id = $attribute->getId();

        if ($this->product->hasAttribute($id)) {
            $attribute = $this->product->removeAttribute($id);
            $attributeExists = true;
        } else {
            $attribute = $this->service->getAttribute($id);
            $attributeExists = false;
        }

        if ($attribute === null) {
            throw new Exception(__('Attribute does not exists.', 'jigoshop'));
        }

        $this->populateAttribute($attribute, $attributeExists, $data);

        $this->addAndSaveAttribute($attribute);

        return $response->withJson([
            'success' => true,
            'data' => "Attribute successfully updated",
        ]);
    }

    /**
     * setting product
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

    /**
     * @param Request $request
     * @param Response $response
     * @param $args
     */
    public function delete(Request $request, Response $response, $args)
    {
        // TODO: Implement delete() method.
    }

    /**
     * @param array $args
     * @return ProductEntity\Attribute[]
     */
    protected function getObjects(array $args)
    {
        /** @var ProductService $service */
        $service = $this->service;
        return $service->getAttributes($this->product->getId());
    }

    /**
     * @return int
     */
    protected function getObjectsCount()
    {
        /** @var ProductService $service */
        $service = $this->service;
        $items = $service->getAttributes($this->product->getId());
        return count($items);
    }

    /**
     * find attribute and validate it's
     * @param $args
     * @return mixed
     */
    protected function validateObjectFinding($args)
    {
        if (!isset($args['id']) || empty($args['id'])) {
            throw new Exception("$this->entityName ID was not provided");
        }

        $object = $this->service->getAttribute($args['id']);
        $entity = self::JIGOSHOP_ENTITY_PREFIX . 'Product\\' . ucfirst($this->entityName);

        if (!$object instanceof $entity) {
            throw new Exception("$this->entityName not found.", 404);
        }

        return $object;
    }

    /**
     * set attribute with given values
     * @param Attribute $attribute
     * @param $attributeExists
     * @param $data
     */
    protected function populateAttribute(&$attribute, $attributeExists, $data)
    {
        if (isset($data['value'])) {
            $attribute->setValue(trim(htmlspecialchars(wp_kses_post($data['value']))));
        } else {
            if ($attributeExists) {
                throw new Exception(sprintf(__('Attribute "%s" already exists.', 'jigoshop'), $attribute->getLabel()));
            } else {
                $attribute->setValue('');
            }
        }

        if (isset($data['options']) && isset($data['options']['display'])) {
            $attribute->setVisible($_POST['options']['display'] === 'true');
        }
    }

    /**
     * @param $attribute
     */
    private function addAndSaveAttribute($attribute)
    {
        $wp = $this->app->getContainer()->di->get('wpal');
        $wp->doAction('jigoshop\admin\product_attribute\add', $attribute, $this->product);

        $this->product->addAttribute($attribute);
        $this->service->save($this->product);
    }

}