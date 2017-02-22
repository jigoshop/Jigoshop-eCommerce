<?php

namespace Jigoshop\Api\Routes\V1;

use Jigoshop\Api\Permission;
use Jigoshop\Core\Types;
use Jigoshop\Entity\Coupon as CouponEntity;
use Jigoshop\Exception;
use Jigoshop\Service\CouponService;
use Slim\App;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Class Coupons
 * @package Jigoshop\Api\Routes\V1;
 * @author Maciej Maciaszek
 */
class Coupons
{
    /** @var  App */
    private $app;

    /**
     * Orders constructor.
     * @param App $app
     */
    public function __construct(App $app)
    {
        $this->app = $app;
        $app->get('', array($this, 'getCoupons'));
        $app->get('/{id:[0-9]+}', array($this, 'getCoupon'));
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return Response
     */
    public function getCoupons(Request $request, Response $response, $args)
    {
        if(!$this->app->getContainer()->token->hasPermission(Permission::READ_COUPONS)) {
            throw new Exception('You have no permissions to access to this page.', 401);
        }

        /** @var CouponService $service */
        $service = $this->app->getContainer()->di->get('jigoshop.service.coupon');

        $queryParams = $request->getParams();
        $queryParams['pagelen'] = isset($queryParams['pagelen']) && is_numeric($queryParams['pagelen']) ? (int)$queryParams['pagelen'] : 10;
        $queryParams['page'] = isset($queryParams['page']) && is_numeric($queryParams['page']) ? (int)$queryParams['page'] : 1;
        $coupons = $service->findByQuery(new \WP_Query([
            'post_type' => Types::COUPON,
            'posts_per_page' => $queryParams['pagelen'],
            'paged' => $queryParams['page'],
        ]));

        return $response->withJson([
            'success' => true,
            'all_results' => $service->getCouponsCount(),
            'pagelen' => $queryParams['pagelen'],
            'page' => $queryParams['page'],
            'next' => '',
            'previous' =>  '',
            'data' => array_values($coupons),
        ]);
    }

    public function getCoupon(Request $request, Response $response, $args)
    {
        if(!$this->app->getContainer()->token->hasPermission(Permission::READ_COUPONS)) {
            throw new Exception('You have no permissions to access to this page.', 401);
        }

        if(!isset($args['id']) || empty($args['id'])) {
            throw new Exception(__('Coupon ID was not provided', 'jigoshop'));
        }
        /** @var CouponService $service */
        $service = $this->app->getContainer()->di->get('jigoshop.service.coupon');
        $coupon = $service->find($args['id']);

        if(!$coupon instanceof CouponEntity) {
            throw new Exception(__('Coupon not found.', 'jigoshop'),404);
        }

        return $response->withJson([
            'success' => true,
            'data' => $coupon,
        ]);
    }
}