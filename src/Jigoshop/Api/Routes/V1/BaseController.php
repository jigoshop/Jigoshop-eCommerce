<?php

namespace Jigoshop\Api\Routes\V1;

use Slim\App;
use Slim\Http\Request;
use Slim\Http\Response;
use Jigoshop\Exception;

/**
 * extend this class in order to get RESTful methods for contollers
 * It takes class name to provide service, entity and type. You can override this if there is need by providing
 * $service, $entityName
 * Class BaseControlller
 * @package Jigoshop\Api\Routes\V1
 */
abstract class BaseController
{
    /**
     * prefix to services
     */
    const JIGOSHOP_SERVICE_PREFIX = 'jigoshop.service.';
    /**
     * path to type class
     */
    const JIGOSHOP_TYPES_PREFIX = 'Jigoshop\\Core\\Types::';
    /**
     * path to entities
     */
    const JIGOSHOP_ENTITY_PREFIX = 'Jigoshop\\Entity\\';
    /**
     * @var string
     */
    protected $entityName;
    /**
     * @var string
     */
    protected $serviceName;
    /**
     * @var
     */
    protected $service;
    /**
     * @var App
     */
    protected $app;
    /**
     * number of items per page
     * @var int
     */
    protected $pageLen = 10;

    /**
     * initialize all needed values
     * PostController constructor.
     * @param App $app
     */
    public function __construct(App $app)
    {
        $this->app = $app;
        $this->entityName = $this->entityName ?: $this->singularize(strtolower((new \ReflectionClass($this))->getShortName()));
        $this->serviceName = $this->serviceName ?: self::JIGOSHOP_SERVICE_PREFIX . strtolower((new \ReflectionClass($this))->getShortName());
        $this->service = $this->app->getContainer()->di->get($this->singularize($this->serviceName));
    }

    /**
     * Basic findAll method
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return Response
     */
    public function findAll(Request $request, Response $response, $args)
    {
        $queryParams = $this->setDefaultQueryParams($request->getParams());

        $items = $this->getObjects($queryParams);
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
     * Basic findOne method
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return Response
     */
    public function findOne(Request $request, Response $response, $args)
    {
        if (!isset($args['id']) || empty($args['id'])) {
            throw new Exception("$this->entityName ID was not provided");
        }

        $item = $this->service->find($args['id']);
        $entity = self::JIGOSHOP_ENTITY_PREFIX . ucfirst($this->entityName);

        if (!$item instanceof $entity) {
            throw new Exception("$this->entityName not found.", 404);
        }

        return $response->withJson([
            'success' => true,
            'data' => $item,
        ]);
    }

    /**
     * @param array $queryParams
     * @return mixed
     */
    abstract protected function getObjects(array $queryParams);
    /**
     * @return mixed
     */
    abstract protected function getObjectsCount();

    /**
     * function to shorten plural to singular string
     * @param $string
     * @param string $ending
     * @return string
     */
    protected function singularize($string, $ending = 's')
    {
        return rtrim($string, "$ending");
    }

    /**
     * setting default query params if not provided in request ex. items per page, page number
     * @param $queryParams
     * @return mixed
     */
    private function setDefaultQueryParams($queryParams)
    {
        $queryParams['pagelen'] = isset($queryParams['pagelen']) && is_numeric($queryParams['pagelen'])
            ? (int)$queryParams['pagelen'] : $this->pageLen;
        $queryParams['page'] = isset($queryParams['page']) && is_numeric($queryParams['page'])
            ? (int)$queryParams['page'] : 1;
        return $queryParams;
    }
}