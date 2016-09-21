<?php

namespace Jigoshop\Api\Response\V1;

use Jigoshop\Api\Response\ResponseInterface;
use Jigoshop\Api\Response\V1\Helper\Order as OrderHelper;
use Jigoshop\Api\Validation\Permission;
use Jigoshop\Container;
use Jigoshop\Core\Types;
use Jigoshop\Entity\Order\Status;
use Jigoshop\Exception;
use Jigoshop\Service\OrderService;

/**
 * Class Orders
 * @package Jigoshop\Api\Response\V1;
 * @author Krzysztof Kasowski
 */
class Orders implements ResponseInterface
{
    /** @var array  */
    private $permissions = array();
    /** @var  OrderService */
    private $orderService;

    /**
     * @param Container $di
     * @param array $permissions
     */
    public function init(Container $di, array $permissions)
    {
        $this->permissions = $permissions;
        $this->orderService = $di->get('jigoshop.service.order');
    }

    public function getList()
    {
        if(!in_array(Permission::READ_ORDERS, $this->permissions)) {
            $this->notPermittedRequest();
        }

        $defaults = array(
            'post_type' => Types::ORDER,
            'posts_per_page' => 10,
            'paged' => 1,
            'post_status' => array_keys(Status::getStatuses()),
            //'include' => array(),
        );

        $args = array();
        if (isset($_GET['pagelen'])) {
            $args['posts_per_page'] = (int)$_GET['pagelen'];
        }

        if (isset($_GET['page'])) {
            $args['paged'] = (int)$_GET['page'];
        }

        $args = array_merge($defaults, $args);
        $query = new \WP_Query($args);

        $orders = $this->orderService->findByQuery($query);

        $response = array(
            'found' => $query->found_posts,
            'pages' => $query->max_num_pages,
            'orders' => array(),
        );

        foreach($orders as $order) {
            $response['orders'][] = OrderHelper::getBasicData($order);
        }

        return $response;
    }

    public function getSingle($id)
    {
        $defaults = [
            'ignore' => array(),
        ];
        $args = array_merge($defaults, $_GET);

        $order = $this->orderService->find($id);

        $response = [];
        $response['order'] = OrderHelper::getBasicData($order);

        return $response;
    }

    private function notPermittedRequest()
    {
        throw new Exception();
    }
}