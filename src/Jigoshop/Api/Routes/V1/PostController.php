<?php

namespace Jigoshop\Api\Routes\V1;

use Slim\Http\Request;
use Slim\Http\Response;
use Jigoshop\Exception;

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
     * Basic wp_post creation method
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return Response
     */
    public function create(Request $request, Response $response, $args)
    {
        $this->service->savePost(null);
        return $response->withJson([
            'success' => true,
            'data' => "$this->entityName successfully created",
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
        if (!isset($args['id']) || empty($args['id'])) {
            throw new Exception("$this->entityName ID was not provided");
        }

        $object = $this->service->find($args['id']);
        $entity = self::JIGOSHOP_ENTITY_PREFIX . ucfirst($this->entityName);

        if (!$object instanceof $entity) {
            throw new Exception("$this->entityName not found.", 404);
        }

        $factory = $this->app->getContainer()->di->get("jigoshop.factory.$this->entityName");
        $object = $factory->update($object, $request->getParsedBody()); //updating object with parsed variables

        $service = $this->app->getContainer()->di->get("jigoshop.service.$this->entityName");
        $service->updateAndSavePost($object);

        return $response->withJson([
            'success' => true,
            'data' => "$this->entityName successfully updated",
        ]);
    }

    public function delete(Request $request, Response $response, $args)
    {
        // TODO: Implement delete() method.
    }

    /**
     * return customers by query
     * @param array $queryParams
     * @return mixed
     */
    protected function getObjects(array $queryParams)
    {
        return $this->service->findByQuery(new \WP_Query([
            'post_type' => constant(self::JIGOSHOP_TYPES_PREFIX . strtoupper($this->entityName)),
            'posts_per_page' => $queryParams['pagelen'],
            'paged' => $queryParams['page'],
        ]));
    }

    /**
     * return total number customers
     * @return mixed
     */
    protected function getObjectsCount()
    {
        return call_user_func(array($this->service, 'get' . $this->entityName . 'sCount'));
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