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
 * Class Attributes
 * @package Jigoshop\Api\Routes\V1;
 * @author MAciej Maciaszek
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
     * @apiDefine ProductAttributeReturnObject
     * @apiSuccess {Number}    data.id    The ID.
     * @apiSuccess {String}    data.label Attribute label.
     * @apiSuccess {String}    data.slug Slug.
     * @apiSuccess {Bool}    data.local Defines if variable can be used for all products or just locally.
     * @apiSuccess {Number}    data.type Type of attribute.
     * @apiSuccess {String}    data.key Defines if attribute is visible.
     * @apiSuccess {Bool}    data.exists True if this attribute is in the database.
     * @apiSuccess {Object[]} data.visible Product attribute visibility.
     * @apiSuccess {Number} data.visible.id Option visible label.
     * @apiSuccess {String} data.visible.key Meta name.
     * @apiSuccess {Number} data.visible.value Visibility value.
     * @apiSuccess {Object[]} data.options Array of available options objects for this attribute.
     * @apiSuccess {Number} data.options.id Option visible label.
     * @apiSuccess {String} data.options.label Option visible label.
     * @apiSuccess {Number} data.options.value Value of attribute.
     * @apiSuccess {Number} data.options.value Product attribute chosen or typed value.
     */
    /**
     * @apiDefine ProductAttributeData
     * @apiParam {String} value Value.
     * @apiParam {Array} [options] Set product attribute options.
     * @apiParam {String} [options.display] Set product visibility.
     */

    /**
     * Products constructor.
     * @param App $app
     */
    public function __construct(App $app)
    {
        parent::__construct($app);
        $this->app = $app;

        /**
         * @api {get} /product/:productId/attributes Get Attributes of a Product
         * @apiName FindAllProductAttributes
         * @apiGroup ProductAttributes
         *
         * @apiParam (Url Params) {Number} productId Product unique ID.
         *
         * @apiUse findAllReturnData
         * @apiSuccess {Object[]} data Array of product attributes objects.
         * @apiUse ProductAttributeReturnObject
         * @apiPermission read_products
         */
        $app->get('', [$this, 'findAll']);

        /**
         * @api {get} /product/:productId/attributes/:id Get Attribute information
         * @apiName GetProductAttribute
         * @apiGroup ProductAttributes
         *
         * @apiParam (Url Params) {Number} productId Product unique ID.
         * @apiParam (Url Params) {Number} id Attribute unique ID.
         *
         * @apiSuccess {Object} data Product attribute object.
         * @apiUse ProductAttributeReturnObject
         *
         * @apiError UnprocessableEntity Attribute Id or Product Id was not provided.
         * @apiError ObjectNotFound Product have not been found or it does not have this attribute.
         * @apiPermission read_products
         */
        $app->get('/{id:[0-9]+}', [$this, 'findOne']);

        /**
         * @api {post} /product/:productId/attributes Add attribute to a product
         * @apiName PostProductAttributes
         * @apiGroup ProductAttributes
         *
         * @apiParam (Url Params) {Number} :productId Product unique ID.
         *
         * @apiUse ProductAttributeData
         * @apiUse StandardSuccessResponse
         * @apiPermission manage_products
         */
        $app->post('', [$this, 'create']);

        /**
         * @api {put} /product/:productId/attributes/:id Update product attribute
         * @apiName PutProductAttribute
         * @apiGroup ProductAttributes
         *
         * @apiParam (Url Params) {Number} :productId Product unique ID.
         * @apiParam (Url Params) {Number} :id Attribute unique ID.
         *
         * @apiUse ProductAttributeData
         * @apiUse StandardSuccessResponse
         *
         * @apiError UnprocessableEntity Attribute Id or Product Id was not provided.
         * @apiError ObjectNotFound Product have not been found or it does not have this attribute.
         * @apiPermission manage_products
         */
        $app->put('/{id:[0-9]+}', [$this, 'update']);

        /**
         * @api {delete} /product/:productId/attributes/:id Delete attribute from a product
         * @apiName DeleteProductAttribute
         * @apiGroup ProductAttributes
         *
         * @apiParam (Url Params) {Number} productId Product unique ID.
         * @apiParam (Url Params) {Number} id Attribute unique ID.
         *
         * @apiUse StandardSuccessResponse
         *
         * @apiError UnprocessableEntity Attribute Id or Product Id was not provided.
         * @apiError ObjectNotFound Product have not been found or it does not have this attribute.
         * @apiPermission manage_products
         */
        $app->delete('/{id:[0-9]+}', [$this, 'delete']);
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
     * get specified attribute for product
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return Response
     */
    public function findOne(Request $request, Response $response, $args)
    {
        $this->setProduct($args);
        $attribute = $this->validateObjectFinding($args);
        if (!$this->product->hasAttribute($attribute->getId())) {
            throw new Exception("Product has not this attribute", 404);
        }
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
            throw new Exception(__('Custom attribute requires label to be set.', 'jigoshop-ecommerce'), 422);
        }

        $attribute->setLabel($label);
        $attribute->setSlug($this->wp->getHelpers()->sanitizeTitle($label));
        $this->service->saveAttribute($attribute);
        $attributeExists = false;

        if ($attribute === null) {
            throw new Exception(__('Attribute does not exists.', 'jigoshop-ecommerce'), 404);
        }

        $this->populateAttribute($attribute, $attributeExists, $_POST);

        $this->addAndSaveAttribute($attribute);

        return $response->withJson([
            'success' => true,
            'data' => $attribute,
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

        $id = $attribute->getId();

        if ($this->product->hasAttribute($id)) {
            $attribute = $this->product->removeAttribute($id);
            $attributeExists = true;
        } else {
            $attribute = $this->service->getAttribute($id);
            $attributeExists = false;
        }

        if ($attribute === null) {
            throw new Exception(__('Attribute does not exists.', 'jigoshop-ecommerce'), 404);
        }

        $this->populateAttribute($attribute, $attributeExists, $request->getParsedBody());

        $this->addAndSaveAttribute($attribute);

        return $response->withJson([
            'success' => true,
            'data' => $attribute,
        ]);
    }

    /**
     * remove attribute from product
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return Response
     */
    public function delete(Request $request, Response $response, $args)
    {
        $this->setProduct($args);
        /** @var ProductEntity\Attribute $attribute */
        $attribute = $this->validateObjectFinding($args);
        $id = $attribute->getId();
        if (!$this->product->hasAttribute($id)) {
            throw new Exception("Product has not this attribute", 404);
        }
        $this->product->removeAttribute($id);
        $this->service->save($this->product);
        return $response->withJson([
            'success' => true,
            'data' => $this->product,
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
            throw new Exception("Product Id was not provided", 422);
        }
        $product = $this->service->find($args['productId']);
        if (!$product instanceof ProductEntity) {
            throw new Exception("Product not found.", 404);
        }
        $this->product = $product;
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
            throw new Exception("$this->entityName ID was not provided", 422);
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
                throw new Exception(sprintf(__('Attribute "%s" already exists.', 'jigoshop-ecommerce'), $attribute->getLabel()));
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