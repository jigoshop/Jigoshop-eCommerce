<?php

namespace Jigoshop\Api\Response\V1;

use Jigoshop\Api\Response\ResponseInterface;
use Jigoshop\Container;
use Jigoshop\Core\Types;
use Jigoshop\Api\Response\V1\Helper\Product as ProductHelper;
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
            'include' => array(),
        );

        $args = array();
        if (isset($_GET['pagelen'])) {
            $args['posts_per_page'] = (int)$_GET['pagelen'];
        }
        if (isset($_GET['page'])) {
            $args['paged'] = (int)$_GET['page'];
        }
        if(isset($_GET['include'])) {
            $args['include'] = $_GET['include'];
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
            $productData = ProductHelper::getBasicData($product);
            if(in_array('attributes', $args['include'])) {
                $productData = array_merge($productData, ProductHelper::getAttributes($product));
            }
            if(in_array('categories', $args['include'])) {
                $productData = array_merge($productData, ProductHelper::getCategories($product));
            }
            if(in_array('tags', $args['include'])) {
                $productData = array_merge($productData, ProductHelper::getTags($product));
            }
            $response['products'][] = $productData;
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
        $defaults = [
            'ignore' => array(),
        ];
        $args = array_merge($defaults, $_GET);

        $product = $this->productService->find($id);

        $response = [];
        $productData  = ProductHelper::getBasicData($product);
        if(!in_array('attributes', $args['ignore'])) {
            $productData = array_merge($productData, ProductHelper::getAttributes($product));
        }
        if(!in_array('categories', $args['ignore'])) {
            $productData = array_merge($productData, ProductHelper::getCategories($product));
        }
        if(!in_array('tags', $args['ignore'])) {
            $productData = array_merge($productData, ProductHelper::getTags($product));
        }
        if(!in_array('attachments', $args['ignore'])) {
            $productData = array_merge($productData, ProductHelper::getAttachments($product));
        }
        $response['product'] = $productData;

        return $response;
    }
}