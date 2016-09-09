<?php

namespace Jigoshop\Api\Response\V1;

use Jigoshop\Api\Response\ResponseInterface;
use Jigoshop\Container;
use Jigoshop\Core\Types;
use Jigoshop\Entity\Product;
use Jigoshop\Service\ProductService;

/**
 * Class Products
 * @package Jigoshop\Api\Response\V1;
 * @author Krzysztof Kasowski
 */
class Products implements ResponseInterface
{
    /** @var  ProductService */
    private $productService;
    /** @var  array */
    private $permissions;

    /**
     * @param Container $di
     * @param array $permissions
     */
    public function init(Container $di, array $permissions)
    {
        $this->productService = $di->get('jigoshop.service.product');
        $this->permissions = $permissions;
    }

    public function getList()
    {
        $defaults = array(
            'post_type' => Types::PRODUCT,
            'posts_per_page' => 10,
            'paged' => 1,
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

        $products = $this->productService->findByQuery($query);

        $response = array(
            'found' => $query->found_posts,
            'pages' => $query->max_num_pages,
            'products' => array(),
        );
        foreach ($products as $product) {
            /** @var Product $product */
            $response['products']['product'][] = array(
                    'id' => $product->getId(),
                    'type' => $product->getType(),
                    'name' => $product->getName(),
            );
        }

        return $response;
    }

    /**
     * @param int $id
     *
     * @return array
     */
    public function getSingle($id)
    {
        $product = $this->productService->find($id);

        $response = [];
        $response['product'] = array(
            'id' => $product->getId(),
            'type' => $product->getType(),
            'name' => $product->getName(),
        );

        return $response;
    }
}