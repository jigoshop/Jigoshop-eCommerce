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
     * @apiParam {Array} jigoshop_email Email attributes.
     * @apiParam {String} jigoshop_email.subject Email subject.
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
         * @apiSuccess {Object[]} data List of emails.
         * @apiUse EmailReturnObject
         */
        $app->get('', array($this, 'findAll'));

        /**
         * @api {get} /emails/:id Get Email information
         * @apiName GetEmails
         * @apiGroup Email
         *
         * @apiParam {Number} :id Email unique ID.
         *
         * @apiUse EmailReturnObject
         *
         * @apiUse validateObjectFindingError
         */
        $app->get('/{id:[0-9]+}', array($this, 'findOne'));

        /**
         * @api {post} /emails Create a Email
         * @apiName PostEmail
         * @apiGroup Email
         *
         * @apiUse EmailData
         *
         * @apiUse StandardSuccessResponse
         */
        $app->post('', array($this, 'create'));

        /**
         * @api {put} /emails/:id Update a Email
         * @apiName PutEmail
         * @apiGroup Email
         *
         * @apiParam {Number} :id Email unique ID.
         * @apiUse EmailData
         *
         * @apiUse StandardSuccessResponse
         * @apiUse validateObjectFindingError
         *
         */
        $app->put('/{id:[0-9]+}', array($this, 'update'));

        /**
         * @api {delete} /emails/:id Delete a Email
         * @apiName DeleteEmail
         * @apiGroup Email
         *
         * @apiParam {Number} :id Email unique ID.
         *
         * @apiUse StandardSuccessResponse
         * @apiUse validateObjectFindingError
         */
        $app->delete('/{id:[0-9]+}', array($this, 'delete'));
    }
}