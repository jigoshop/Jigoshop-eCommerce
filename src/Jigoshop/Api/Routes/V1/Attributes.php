<?php

namespace Jigoshop\Api\Routes\V1;

use Jigoshop\Api\Contracts\ApiControllerContract;
use Jigoshop\Exception;
use Jigoshop\Middleware\RequiredFieldsMiddleware;
use Slim\App;
use Slim\Http\Request;
use Slim\Http\Response;
use Jigoshop\Entity\Product\Attribute;

/**
 * @apiDefine Attributes Attributes endpoints
 * Reusable attributes objects that can be added to products and then customized.
 */

/**
 * Class Attributes
 * @package Jigoshop\Api\Routes\V1;
 * @author Maciej Maciaszek
 */
class Attributes extends BaseController implements ApiControllerContract
{
    /** @var  App */
    protected $app;
    /** @var string */
    protected $serviceName = 'jigoshop.service.product';
    /** @var string */
    protected $entityName = 'Product\\Attribute';
    /**
     * set this in order to set permission name that u refer to
     * @var string
     */
    protected $referringPermission = 'products';

    /**
     * @apiDefine AttributeReturnObject
     * @apiSuccess {Number}    data.id    The ID.
     * @apiSuccess {String}    data.label Attribute label.
     * @apiSuccess {String}    data.slug Slug.
     * @apiSuccess {Bool}    data.local Defines if variable can be used for all products or just locally.
     * @apiSuccess {Number}    data.type Type of attribute.
     * @apiSuccess {String}    data.key Defines if attribute is visible.
     * @apiSuccess {Bool}    data.exists True if this attribute is in the database.
     * @apiSuccess {Object[]} data.options Array of available options objects for this attribute.
     * @apiSuccess {String} data.options.label Option visible label.
     * @apiSuccess {Number} data.options.value Value of attribute.
     */
    /**
     * @apiDefine AttributeData
     * @apiParam {String} label Attribute label.
     * @apiParam {Number=0,1,2} type Type of Attribute.
     * @apiParam {String} [slug] Slug (is generated from label if not provided).
     */
    /**
     * @apiDefine AttributeOptionData
     * @apiParam {String} label Option visible label.
     * @apiParam {Number} value Option value.
     */
    /**
     * @apiDefine AttributeOptionReturnObject
     * @apiSuccess {String} label Option visible label.
     * @apiSuccess {Number} value Option value.
     */

    /**
     * Attributes constructor.
     * @param App $app
     */
    public function __construct(App $app)
    {
        parent::__construct($app);
        $this->app = $app;

        /**
         * @api {get} /attributes Get Attributes
         * @apiName FindAllAttributes
         * @apiGroup Attributes
         *
         * @apiUse findAllReturnData
         * @apiSuccess {Object[]} data List of available attribute objects.
         * @apiUse AttributeReturnObject
         * @apiPermission read_products
         */
        $app->get('', [$this, 'findAll']);

        /**
         * @api {get} /attributes/:id Get Attribute information
         * @apiName GetAttribute
         * @apiGroup Attributes
         *
         * @apiParam (Url Params) {Number} id Attribute unique ID.
         *
         * @apiSuccess {Object} data Single attribute object.
         * @apiUse AttributeReturnObject
         * @apiUse validateObjectFindingError
         * @apiPermission read_products
         */
        $app->get('/{id:[0-9]+}', [$this, 'findOne']);

        /**
         * @api {post} /attributes Create attribute
         * @apiName PostAttributes
         * @apiGroup Attributes
         *
         * @apiUse AttributeData
         * @apiUse StandardSuccessResponse
         */
        $app->post('', [$this, 'create'])->add(new RequiredFieldsMiddleware($app));

        /**
         * @api {put} /attributes/:id Update attribute
         * @apiName PutAttribute
         * @apiGroup Attributes
         *
         * @apiParam (Url Params) {Number} id Attribute unique ID.
         *
         * @apiUse AttributeData
         * @apiUse StandardSuccessResponse
         * @apiUse validateObjectFindingError
         */
        $app->put('/{id:[0-9]+}', [$this, 'update'])->add(new RequiredFieldsMiddleware($app));

        /**
         * @api {delete} /attributes/:id Delete attribute
         * @apiName DeleteAttribute
         * @apiGroup Attributes
         *
         * @apiParam (Url Params) {Number} id Attribute unique ID.
         *
         * @apiUse StandardSuccessResponse
         * @apiUse validateObjectFindingError
         */
        $app->delete('/{id:[0-9]+}', [$this, 'delete']);

        //options single routes
        /**
         * @api {post} /attributes/:id/options Create attribute option
         * @apiName PostAttributeOption
         * @apiGroup Attributes
         *
         * @apiParam (UrlParams) {Number} id Attribute unique ID.
         *
         * @apiUse StandardSuccessResponse
         * @apiUse AttributeOptionData
         * @apiError UnprocessableEntity Attribute Id was not provided.
         * @apiError ObjectNotFound Attribute have not been found.
         */
        $app->post('/{id:[0-9]+}/options', [$this, 'addOption'])
            ->add(new RequiredFieldsMiddleware($app, ['requirementsName' => 'attributeOptions']));

        /**
         * @api {put} /attributes/:id/options/:id Update attribute option
         * @apiName PutAttributeOption
         * @apiGroup Attributes
         *
         * @apiParam (Url Params) {Number} id Attribute unique ID.
         * @apiParam (Url Params) {Number} optionId Attribute option unique ID.
         *
         * @apiUse AttributeOptionData
         *
         * @apiUse StandardSuccessResponse
         * @apiError UnprocessableEntity Attribute Id or Option Id was not provided.
         * @apiError ObjectNotFound Attribute have not been found or it does not have this option.
         */
        $app->put('/{id:[0-9]+}/options/{optionId:[0-9]+}', [$this, 'updateOption']);

        /**
         * @api {delete} /attributes/:id/options/:id Delete attribute option
         * @apiName DeleteAttributeOption
         * @apiGroup Attributes
         *
         * @apiParam (Url Params) {Number} id Attribute unique ID.
         * @apiParam (Url Params) {Number} optionId Attribute option unique ID.
         *
         * @apiUse StandardSuccessResponse
         * @apiError UnprocessableEntity Attribute Id or Option Id was not provided.
         * @apiError ObjectNotFound Attribute have not been found or it does not have this option.
         */
        $app->delete('/{id:[0-9]+}/options/{optionId:[0-9]+}', [$this, 'deleteOption']);
    }

    /**
     * create attribute
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return Response
     */
    public function create(Request $request, Response $response, $args)
    {
        $this->saveAttribute($_POST);
        return $response->withJson([
            'success' => true,
            'data' => "Attribute successfully created",
        ]);
    }

    /**
     * update attribute
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return Response
     */
    public function update(Request $request, Response $response, $args)
    {
        $this->validateObjectFinding($args);
        $this->saveAttribute($request->getParsedBody(), $args['id']);
        return $response->withJson([
            'success' => true,
            'data' => "Attribute successfully updated",
        ]);
    }

    /**
     * remove attribute
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return Response
     */
    public function delete(Request $request, Response $response, $args)
    {
        $attribute = $this->validateObjectFinding($args);
        $this->service->removeAttribute($attribute->getId());
        return $response->withJson([
            'success' => true,
            'data' => "Attribute successfully deleted",
        ]);
    }

    /**
     * add option to Attribute
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return Response
     */
    public function addOption(Request $request, Response $response, $args)
    {
        $this->validateObjectFinding($args);
        $this->saveOption($args['id'], $_POST);
        return $response->withJson([
            'success' => true,
            'data' => "Attribute option successfully created",
        ]);
    }

    /**
     * update attribute option
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return Response
     */
    public function updateOption(Request $request, Response $response, $args)
    {
        $attribute = $this->validateObjectFinding($args);
        $this->validateOptionFinding($attribute, $args);
        $this->saveOption($args['id'], $request->getParsedBody(), $args['optionId']);
        return $response->withJson([
            'success' => true,
            'data' => "Attribute option successfully updated",
        ]);
    }

    /**
     * remove attribute option
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return Response
     */
    public function deleteOption(Request $request, Response $response, $args)
    {
        $attribute = $this->validateObjectFinding($args);
        $this->validateOptionFinding($attribute, $args);
        $this->service->removeAttribute($args['optionId']);
        return $response->withJson([
            'success' => true,
            'data' => "Attribute successfully deleted",
        ]);
    }


    public function getObjects(array $queryParams)
    {
        return $this->service->findAllAttributes();
    }

    protected function getObjectsCount()
    {
        return $this->service->countAttributes();
    }

    /**
     * validates if correct attribute object was found
     * @param $args
     * @return mixed
     */
    protected function validateObjectFinding($args)
    {
        if (!isset($args['id']) || empty($args['id'])) {
            throw new Exception("Attribute ID was not provided", 422);
        }

        $object = $this->service->getAttribute($args['id']);
        $entity = self::JIGOSHOP_ENTITY_PREFIX . ucfirst($this->entityName);
        if (!$object instanceof $entity) {
            throw new Exception("Attribute not found.", 404);
        }

        return $object;
    }

    /**
     * validates if correct attribute option object was found
     * @param Attribute $attribute
     * @param $args
     */
    private function validateOptionFinding($attribute, $args)
    {
        if (!isset($args['optionId']) || empty($args['optionId'])) {
            throw new Exception("option ID was not provided", 422);
        }
        if (!$attribute->getOption($args['optionId'])) {
            throw new Exception("Attribute does not have this option.", 404);
        }
    }

    //todo can be moved to service
    /**
     * @param $data
     * @param null $attributeId
     */
    private function saveAttribute($data, $attributeId = null)
    {
        $errors = [];
        if (!isset($data['label']) || empty($data['label'])) {
            $errors[] = __('Attribute label is not set.', 'jigoshop-ecommerce');
        }
        if (!isset($data['type']) ||
            !(is_numeric($data['type']) && in_array((int)$data['type'], array_keys(Attribute::getTypes()), true))
        ) {
            throw new Exception(__('Attribute type is not valid.', 'jigoshop-ecommerce'), 422);
        }

        $attribute = $this->service->createAttribute((int)$data['type']);

        if ($attributeId) {
            $baseAttribute = $this->service->getAttribute($attributeId);
            $attribute->setId($baseAttribute->getId());
            $attribute->setOptions($baseAttribute->getOptions());
        }

        $attribute->setLabel(trim(htmlspecialchars(strip_tags($data['label']))));

        if (isset($data['slug']) && !empty($data['slug'])) {
            $attribute->setSlug(trim(htmlspecialchars(strip_tags($data['slug']))));
        } else {
            $wp = $this->app->getContainer()->di->get('wpal');
            $attribute->setSlug($wp->getHelpers()->sanitizeTitle($attribute->getLabel()));
        }

        $this->service->saveAttribute($attribute);
    }

    /**
     * @param $attributeId
     * @param $data
     * @param $optionId
     */
    protected function saveOption($attributeId, $data, $optionId = null)
    {
        $attribute = $this->service->getAttribute($attributeId);
        if ($optionId) {
            $option = $attribute->removeOption($optionId);
        } else {
            $option = new Attribute\Option();
        }

        $option->setLabel(trim(htmlspecialchars(strip_tags($data['label']))));

        if (isset($data['value']) && !empty($data['value'])) {
            $option->setValue(trim(htmlspecialchars(strip_tags($data['value']))));
        } else {
            $wp = $this->app->getContainer()->di->get('wpal');
            $option->setValue($wp->getHelpers()->sanitizeTitle($option->getLabel()));
        }

        $attribute->addOption($option);
        $this->service->saveAttribute($attribute);
    }
}