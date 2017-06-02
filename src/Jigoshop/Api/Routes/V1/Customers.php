<?php

namespace Jigoshop\Api\Routes\V1;

use Jigoshop\Api\Contracts\ApiControllerContract;
use Jigoshop\Exception;
use Jigoshop\Middleware\RequiredFieldsMiddleware;
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
     * @apiDefine CustomerReturnObject
     * @apiSuccess {Number} data.id    The ID.
     * @apiSuccess {String} data.login Customer login.
     * @apiSuccess {String} data.email Customer email.
     * @apiSuccess {String} data.name Customer name.
     * @apiSuccess {Object} data.billing Customer's billing data.
     * @apiSuccess {String} data.billing.first_name Billing first name.
     * @apiSuccess {String} data.billing.last_name Billing last name.
     * @apiSuccess {String} data.billing.address Billing address.
     * @apiSuccess {String} data.billing.city Billing city.
     * @apiSuccess {String} data.billing.postcode Billing postcode.
     * @apiSuccess {String} data.billing.country Billing country code.
     * @apiSuccess {String} data.billing.state Billing state.
     * @apiSuccess {String} data.billing.email Billing email.
     * @apiSuccess {String} data.billing.phone Billing phone.
     * @apiSuccess {Object} data.shipping Customer's shipping data.
     * @apiSuccess {String} data.shipping.first_name Shipping first name.
     * @apiSuccess {String} data.shipping.last_name Shipping last name.
     * @apiSuccess {String} data.shipping.address Shipping address.
     * @apiSuccess {String} data.shipping.city Shipping city.
     * @apiSuccess {String} data.shipping.postcode Shipping postcode.
     * @apiSuccess {String} data.shipping.country Shipping country code.
     * @apiSuccess {String} data.shipping.state Shipping state.
     * @apiSuccess {String} data.shipping.email Shipping email.
     * @apiSuccess {String} data.shipping.phone Shipping phone.
     * @apiSuccess {String} data.taxAddres Address type chosen for tax.
     */
    /**
     * @apiDefine CustomerData
     * @apiParam {Object} billing Customer's billing  
     * @apiParam {String}  billing.first_name Billing first name.
     * @apiParam {String}  billing.last_name Billing last name.
     * @apiParam {String}  billing.address Billing address.
     * @apiParam {String}  billing.city Billing city.
     * @apiParam {String}  billing.postcode Billing postcode.
     * @apiParam {String}  billing.country Billing country code.
     * @apiParam {String}  billing.state Billing state.
     * @apiParam {String}  billing.email Billing email.
     * @apiParam {String}  billing.phone Billing phone.
     * @apiParam {Object}  shipping Customer's shipping  
     * @apiParam {String}  shipping.first_name Shipping first name.
     * @apiParam {String}  shipping.last_name Shipping last name.
     * @apiParam {String}  shipping.address Shipping address.
     * @apiParam {String}  shipping.city Shipping city.
     * @apiParam {String}  shipping.postcode Shipping postcode.
     * @apiParam {String}  shipping.country Shipping country code.
     * @apiParam {String}  shipping.state Shipping state.
     * @apiParam {String}  shipping.email Shipping email.
     * @apiParam {String}  shipping.phone Shipping phone.
     * @apiParam {String}  taxAddres Address type chosen for tax.
     */

    /**
     * Coupons constructor.
     * @param App $app
     */
    public function __construct(App $app)
    {
        parent::__construct($app);
        $this->app = $app;

        /**
         * @api {get} /customers Get Customers
         * @apiName FindCustomers
         * @apiGroup Customer
         *
         * @apiUse findAllReturnData
         * @apiSuccess {Object[]} data Array of customers objects.
         * @apiUse CustomerReturnObject
         * @apiPermission read_customers
         */
        $app->get('', [$this, 'findAll']);

        /**
         * @api {get} /customers/:id Get Customer information
         * @apiName GetCustomers
         * @apiGroup Customer
         *
         * @apiParam (Url Params) {Number} id Customer unique ID.
         *
         * @apiSuccess {Object} data Customer object.
         * @apiUse CustomerReturnObject
         *
         * @apiUse validateObjectFindingError
         * @apiPermission read_customers
         */
        $app->get('/{id:[0-9]+}', [$this, 'findOne']);

        /**
         * @api {post} /customers Create a Customer
         * @apiName PostCustomer
         * @apiGroup Customer
         *
         * @apiUse CustomerData
         *
         * @apiUse StandardSuccessResponse
         * @apiPermission manage_customers
         */
        $app->post('', [$this, 'create'])->add(new RequiredFieldsMiddleware($app));

        /**
         * @api {put} /customers/:id Update a Customer
         * @apiName PutCustomer
         * @apiGroup Customer
         *
         * @apiParam (Url Params) {Number} id Customer unique ID.
         * @apiUse CustomerData
         *
         * @apiUse StandardSuccessResponse
         * @apiUse validateObjectFindingError
         * @apiPermission manage_customers
         */
        $app->put('/{id:[0-9]+}', [$this, 'update']);

        /**
         * @api {delete} /customers/:id Delete a Customer
         * @apiName DeleteCustomer
         * @apiGroup Customer
         *
         * @apiParam (Url Params) {Number} id Customer unique ID.
         *
         * @apiUse StandardSuccessResponse
         * @apiUse validateObjectFindingError
         * @apiPermission manage_customers
         */
        $app->delete('/{id:[0-9]+}', [$this, 'delete']);
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
        $this->validateObjectFinding($args);
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
        if (username_exists($_POST['user_login'])) {
            throw new Exception("Customer username is taken", 422);
        } elseif (email_exists($_POST['user_email'])) {
            throw new Exception("Customer email exists", 422);
        }
        $userId = wp_insert_user([
            'user_login' => $_POST['user_login'],
            'user_pass' => $_POST['user_pass'],
            'user_email' => $_POST['user_email']
        ]);
        if ($userId->errors) {
            return $response->withJson([
                'success' => false,
                'data' => $userId->errors,
            ]);
        }
        $user = new \WP_User($userId);
        $user->set_role('customer');
        $this->service->find($userId);

        return $response->withJson([
            'success' => true,
            'data' => $this->service->find($userId),
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
            'data' => $user,
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
     * @return \WP_User
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