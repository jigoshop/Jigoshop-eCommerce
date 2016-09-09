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
            $response['products']['product'][] = $this->getProductBasicData($product);
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
        $response['product'] = $this->getProductBasicData($product);

        return $response;
    }

    /**
     * @param Product $product
     *
     * @return array
     */
    private function getProductBasicData($product)
    {
        $data = array(
            'id' => $product->getId(),
            'type' => $product->getType(),
            'name' => $product->getName(),
            'description' => $product->getDescription(),
            'sku' => $product->getSku(),
            'brand' => $product->getBrand(),
            'mpn' => $product->getMpn(),
            'gtin' => $product->getGtin(),
            'visibility' => $product->getVisibility(),
            'tax_classes' => array(
                'tax_class' => $product->getTaxClasses()
            ),
            'size' => array(
                'height' => $product->getSize()->getHeight(),
                'length' => $product->getSize()->getLength(),
                'width' => $product->getSize()->getWidth(),
                'weight' => $product->getSize()->getWeight()
            ),
            'link' => $product->getLink(),
        );

        if($product instanceof Product\Simple) {
            $data = array_merge($data, array(
                'regular_price' => $product->getRegularPrice(),
                'sale' => array(
                    'enabled' => $product->getSales()->isEnabled(),
                    'price' => $product->getSales()->getPrice(),
                    'from' => array(
                        'timestamp' => $product->getSales()->getFrom()->getTimestamp(),
                        'date' => $product->getSales()->getFrom()->format('Y-m-d'),
                    ),
                    'to' => array(
                        'timestamp' => $product->getSales()->getTo()->getTimestamp(),
                        'date' => $product->getSales()->getTo()->format('Y-m-d'),
                    ),
                ),
            ));
        }

        return $data;
    }
}