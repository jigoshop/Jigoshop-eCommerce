<?php

namespace Jigoshop\Api\Routes\V1;

use Jigoshop\Api\Contracts\ApiControllerContract;
use Jigoshop\Entity\Customer;
use Jigoshop\Exception;
use Slim\App;
use Slim\Http\Request;
use Slim\Http\Response;
use WPAL\Wordpress;

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
        $app->delete('/{id:[0-9]+}', array($this, 'delete'));
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
        $item = $this->validateObjectFinding($args);
        $item = $this->service->find($args['id']);
        return $response->withJson([
            'success' => true,
            'data' => $item,
        ]);
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
            throw new Exception("Customer username is taken", 422);
        } elseif (email_exists($_POST['user_email'])) {
            throw new Exception("Customer email exists", 422);
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

    /**
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return Response
     */
    public function update(Request $request, Response $response, $args)
    {
        $user = $this->validateObjectFinding($args);
        $putData = $request->getParsedBody();
        $this->validateAddresses($putData);
        /** @var \Jigoshop\Factory\Customer $factory */
        $factory = $this->app->getContainer()->di->get('jigoshop.factory.customer');
        $user = $factory->update($user, $putData);
        $this->service->save($user);
        return $response->withJson([
            'success' => true,
            'data' => "Customer successfully updated",
        ]);

    }

    /**
     *
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return Response
     */
    public function delete(Request $request, Response $response, $args)
    {
        throw new Exception("Removing customers not available from API.", 400);
        // TODO: Implement delete() method. if needed
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

    private function validateAddresses(&$data)
    {
        if (isset($data['billing']) && is_array($data['billing'])) {
            $this->addressConverter($data['billing']);
        }
        if (isset($data['shipping']) && is_array($data['shipping'])) {
            $this->addressConverter($data['shipping']);
        }
    }

    private function addressConverter(array &$address)
    {
        $address = serialize($address);
    }

    /**
     * validates if correct post was found
     * @param $args
     * @return bool|\WP_User
     */
    protected function validateObjectFinding($args)
    {
        if (!isset($args['id']) || empty($args['id'])) {
            throw new Exception("$this->entityName ID was not provided", 422);
        }

        /** @var Wordpress $wp */
        $wp = $this->app->getContainer()->di->get('wpal');
        $object = $wp->getUserBy('id', $args['id']);

        if (!$object || !in_array('customer', (array)$object->roles)) {
            throw new Exception("$this->entityName not found.", 404);
        }

        return $object;
    }
}