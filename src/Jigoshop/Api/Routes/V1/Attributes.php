<?php

namespace Jigoshop\Api\Routes\V1;

use Jigoshop\Api\Contracts\ApiControllerContract;
use Jigoshop\Exception;
use Slim\App;
use Slim\Http\Request;
use Slim\Http\Response;

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
    }

    public function create(Request $request, Response $response, $args)
    {
        $this->saveAttribute($_POST);

    }

    public function update(Request $request, Response $response, $args)
    {
        $this->validateObjectFinding($args);
        $this->saveAttribute($request->getParsedBody());
    }

    public function delete(Request $request, Response $response, $args)
    {
        $this->validateObjectFinding($args);
        $this->service->removeAttribute($id);
    }

    private function saveAttribute($data)
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

        if (isset($data['id']) && is_numeric($data['id'])) {
            $baseAttribute = $this->service->getAttribute((int)$data['id']);
            $attribute->setId($baseAttribute->getId());
            $attribute->setOptions($baseAttribute->getOptions());
        }

        $attribute->setLabel(trim(htmlspecialchars(strip_tags($data['label']))));

        if (isset($data['slug']) && !empty($data['slug'])) {
            $attribute->setSlug(trim(htmlspecialchars(strip_tags($data['slug']))));
        } else {
            $attribute->setSlug($this->wp->getHelpers()->sanitizeTitle($attribute->getLabel()));
        }

        $this->service->saveAttribute($attribute);
    }

    public function getObjects(array $queryParams)
    {
        return $this->service->findAllAttributes();
    }

    protected function getObjectsCount()
    {
        return $this->service->countAttributes();
    }
}