<?php

namespace Jigoshop\Api\Routes\V1;


use Jigoshop\Service\ServiceInterface;
use Slim\App;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * extend this class in order to get RESTful methods for PostArray
 * It takes class name to provide service, entity and type. You can override this if there is need by providing
 * $service, $entityName
 * Class PostController
 * @package Jigoshop\Api\Routes\V1
 */
abstract class PostController extends BaseController
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
     * @var ServiceInterface
     */
    protected $service;
    /**
     * @var App
     */
    protected $app;
    /**
     * @var \WP_Query
     */
    protected $query;

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
     * Basic wp_post creation method
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return Response
     */
    public function create(Request $request, Response $response, $args)
    {
        $object = $this->service->savePost(null);
        return $response->withJson([
            'success' => true,
            'data' => $object,
        ]);
    }

    /**
     * Basic wp_post updating method
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return Response
     */
    public function update(Request $request, Response $response, $args)
    {
        $object = $this->validateObjectFinding($args);

        $factory = $this->app->getContainer()->di->get("jigoshop.factory.$this->entityName");
        $object = $factory->update($object, $request->getParsedBody()); //updating object with parsed variables

        $service = $this->app->getContainer()->di->get("jigoshop.service.$this->entityName");
        $service->updateAndSavePost($object);

        return $response->withJson([
            'success' => true,
            'data' => $object,
        ]);
    }

    /**
     * post deleting method
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return Response
     */
    public function delete(Request $request, Response $response, $args)
    {
        $this->validateObjectFinding($args);
        $result = wp_trash_post($args['id']);
        if (!$result) {
            return $response->withJson([
                'success' => false,
                'data' => "$this->entityName couldn't or is already deleted",
            ]);
        }
        return $response->withJson([
            'success' => true,
            'data' => "$this->entityName successfully deleted",
        ]);
    }

    /**
     * return customers by query
     * @param array $queryParams
     * @return mixed
     */
    protected function getObjects(array $queryParams)
    {
        $this->query = new \WP_Query([
            'post_type' => constant(self::JIGOSHOP_TYPES_PREFIX . strtoupper($this->entityName)),
            'posts_per_page' => $queryParams['pagelen'],
            'paged' => $queryParams['page'],
        ]);

        return $this->service->findByQuery($this->query);
    }

    /**
     * return total number customers
     * @return mixed
     */
    protected function getObjectsCount()
    {
        return $this->query->found_posts;
    }

    /**
     * function to shorten plural to singular strings
     *
     * @param $string
     * @param string $ending
     * @return string
     */
    protected function singularize($string, $ending = 's')
    {
        return rtrim($string, "$ending");
    }

}