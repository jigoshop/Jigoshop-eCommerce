<?php

namespace Jigoshop\Api\Routes\V1;

use Jigoshop\Api\Contracts\ApiControllerContract;
use Slim\App;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Class Customers
 * @package Jigoshop\Api\Routes\V1;
 * @author Maciej Maciaszek
 */
class Customers extends BaseController implements ApiControllerContract
{
    /** @var  App */
    protected $app;

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
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param $args
     */
    public function create(Request $request, Response $response, $args)
    {
        // TODO: Implement create() method.
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param $args
     */
    public function update(Request $request, Response $response, $args)
    {
        // TODO: Implement update() method.
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
     * return customers by query
     * @param array $queryParams
     * @return mixed
     */
    protected function getObjects(array $queryParams)
    {
        return $this->service->findByQuery([
            'role' => 'Customer',
            'number' => $queryParams['pagelen'],
            'offset' => ($queryParams['page'] - 1) * $queryParams['pagelen'],
        ]);
    }

    /**
     * return total number customers
     * @return mixed
     */
    protected function getObjectsCount()
    {
        return (new \WP_User_Query(['role' => 'Customer']))->get_total();
    }

}