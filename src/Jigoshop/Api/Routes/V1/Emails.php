<?php

namespace Jigoshop\Api\Routes\V1;

use Jigoshop\Api\Contracts\ApiControllerContract;
use Slim\App;

/**
 * Class Emails
 * @package Jigoshop\Api\Routes\V1;
 * @author Maciej Maciaszek
 */
class Emails extends PostController implements ApiControllerContract
{
    /** @var  App */
    protected $app;


    /**
     * @apiDefine EmailReturnObject
     * @apiSuccess {Number} data.id    The ID.
     * @apiSuccess {String} data.title Email title.
     * @apiSuccess {String} data.text Email content in html format.
     * @apiSuccess {String} data.subject Email subject.
     */
    /**
     * @apiDefine EmailData
     * @apiParam {String} post_title Email title.
     * @apiParam {String} [content] Email content in html format.
     * @apiParam {Array} [jigoshop_email] Email attributes.
     * @apiParam {String} [jigoshop_email.subject] Email subject.
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
         * @api {get} /emails Get Emails
         * @apiName FindEmails
         * @apiGroup Email
         *
         * @apiUse findAllReturnData
         * @apiSuccess {Object[]} data Array of emails objects.
         * @apiUse EmailReturnObject
         * @apiPermission read_emails
         */
        $app->get('', [$this, 'findAll']);

        /**
         * @api {get} /emails/:id Get Email information
         * @apiName GetEmails
         * @apiGroup Email
         *
         * @apiParam (Url Params) {Number} id Email unique ID.
         *
         * @apiSuccess {Object} data Email object.
         * @apiUse EmailReturnObject
         *
         * @apiUse validateObjectFindingError
         * @apiPermission manage_emails
         */
        $app->get('/{id:[0-9]+}', [$this, 'findOne']);

        /**
         * @api {post} /emails Create a Email
         * @apiName PostEmail
         * @apiGroup Email
         *
         * @apiUse EmailData
         *
         * @apiUse StandardSuccessResponse
         * @apiPermission manage_emails
         */
        $app->post('', [$this, 'create']);

        /**
         * @api {put} /emails/:id Update a Email
         * @apiName PutEmail
         * @apiGroup Email
         *
         * @apiParam (Url Params) {Number} id Email unique ID.
         * @apiUse EmailData
         *
         * @apiUse StandardSuccessResponse
         * @apiUse validateObjectFindingError
         * @apiPermission manage_emails
         */
        $app->put('/{id:[0-9]+}', [$this, 'update']);

        /**
         * @api {delete} /emails/:id Delete a Email
         * @apiName DeleteEmail
         * @apiGroup Email
         *
         * @apiParam (Url Params) {Number} id Email unique ID.
         *
         * @apiUse StandardSuccessResponse
         * @apiUse validateObjectFindingError
         * @apiPermission manage_emails
         */
        $app->delete('/{id:[0-9]+}', [$this, 'delete']);
    }
}