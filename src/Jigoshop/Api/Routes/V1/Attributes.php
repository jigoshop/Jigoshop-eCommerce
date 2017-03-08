<?php

namespace Jigoshop\Api\Routes\V1;

use Jigoshop\Api\Contracts\ApiControllerContract;
use Jigoshop\Exception;
use Slim\App;
use Slim\Http\Request;
use Slim\Http\Response;
use Jigoshop\Entity\Product\Attribute;

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
     * Coupons constructor.
     * @param App $app
     */
    public function __construct(App $app)
    {
        parent::__construct($app);
        $this->app = $app;
        $app->get('', array($this, 'findAll'));
        $app->get('/{id:[0-9]+}', array($this, 'findOne'));
        $app->post('', array($this, 'create'));
        $app->put('/{id:[0-9]+}', array($this, 'update'));
        $app->delete('/{id:[0-9]+}', array($this, 'delete'));

        //options single routes
        $app->post('/{id:[0-9]+}/options', array($this, 'addOption'));
        $app->put('/{id:[0-9]+}/options/{optionId:[0-9]+}', array($this, 'updateOption'));
        $app->delete('/{id:[0-9]+}/options/{optionId:[0-9]+}', array($this, 'deleteOption'));
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
        $attribute = $this->validateObjectFinding($args);
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
        $attribute = $this->validateObjectFinding($args);
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
            throw new Exception("Attribute ID was not provided");
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
     * @param $attribute
     * @param $args
     * @return mixed
     */
    private function validateOptionFinding($attribute, $args)
    {
        if (!isset($args['optionId']) || empty($args['optionId'])) {
            throw new Exception("option ID was not provided");
        }
        if (!$attribute->getOption($args['optionId'])) {
            throw new Exception("Attribute does not have this option.", 404);
        }
    }

    private function saveAttribute($data, $attributeId = null)
    {
        $errors = array();
        if (!isset($data['label']) || empty($data['label'])) {
            $errors[] = __('Attribute label is not set.', 'jigoshop');
        }
        if (!isset($data['type']) || !in_array($data['type'], array_keys(Attribute::getTypes()))) {
            $errors[] = __('Attribute type is not valid.', 'jigoshop');
        }

        if (!empty($errors)) {
            throw new Exception(join('<br/>', $errors));
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
        $errors = array();
        if (!isset($data['label']) || empty($data['label'])) {
            $errors[] = __('Option label is not set.', 'jigoshop');
        }

        if (!empty($errors)) {
            throw new Exception(join('<br/>', $errors));
        }

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