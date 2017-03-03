<?php

namespace Jigoshop\Api\Routes\V1;

use Jigoshop\Api\Contracts\ApiControllerContract;
use Jigoshop\Entity\Customer;
use Jigoshop\Exception;
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
     * crete new user with customer role
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return Response
     */
    public function create(Request $request, Response $response, $args)
    {
        $wp = $this->app->getContainer()->di->get('wpal');
        if (username_exists($_POST['user_login'])) {
            return $response->withJson([
                'success' => false,
                'data' => "Customer username is taken",
            ]);
        } elseif (email_exists($_POST['user_email'])) {
            return $response->withJson([
                'success' => false,
                'data' => "Customer email exists",
            ]);
        }
        $userId = $wp->insert($_POST['user_login'], $_POST['user_pass'], $_POST['user_email']);
        if ($userId->errors) {
            return $response->withJson([
                'success' => false,
                'data' => $userId->errors,
            ]);
        }
        $user = new \WP_User($userId);
        $user->set_role('customer');

        return $response->withJson([
            'success' => true,
            'data' => "Customer successfully created",
        ]);
    }

    //TODO check ability to change login, email, password
    /**
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

        $user = $this->service->find($args['id']);

        if (!$user instanceof Customer) {
            throw new Exception("$this->entityName not found.", 404);
        }

        $user = ($this->app->getContainer()->di->get('jigoshop.factory.customer'))->update($user, $request->getParsedBody());
        $this->service->save($user);
        return $response->withJson([
            'success' => true,
            'data' => "Customer successfully updated",
        ]);

    }

    /**
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return Response
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