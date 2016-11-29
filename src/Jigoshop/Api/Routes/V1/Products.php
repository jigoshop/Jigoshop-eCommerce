<?php

namespace Jigoshop\Api\Routes\V1;

use Jigoshop\Core\Types;
use Jigoshop\Entity\Product as ProductEntity;
use Jigoshop\Exception;
use Jigoshop\Service\ProductService;
use Slim\App;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Class Products
 * @package Jigoshop\Api\Routes\V1;
 * @author Krzysztof Kasowski
 */
class Products
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
        $app->get('', array($this, 'getProducts'));
        $app->get('/{id:[0-9]+}', array($this, 'getProduct'));
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return Response
     */
    public function getProducts(Request $request, Response $response, $args)
    {
        /** @var ProductService $service */
        $service = $this->app->getContainer()->di->get('jigoshop.service.product');

        $queryParams = $request->getParams();
        $queryParams['pagelen'] = isset($queryParams['pagelen']) && is_numeric($queryParams['pagelen']) ? (int)$queryParams['pagelen'] : 10;
        $queryParams['page'] = isset($queryParams['page']) && is_numeric($queryParams['page']) ? (int)$queryParams['page'] : 1;
        $allProducts = $service->getProductsCount();

        $products = $service->findByQuery(new \WP_Query([
            'post_type' => Types::PRODUCT,
            'posts_per_page' => $queryParams['pagelen'],
            'paged' => $queryParams['page'],
        ]));

        $that = $this;

        return $response->withJson([
            'success' => true,
            'all_results' => $allProducts,
            'pagelen' => $queryParams['pagelen'],
            'page' => $queryParams['page'],
            'next' => '',
            'previous' =>  '',
            'data' => array_map(function($product) use ($that) {
                return $that->getProductData($product);
            }, array_values($products)),
        ]);
    }

    public function getProduct(Request $request, Response $response, $args)
    {
        if(!isset($args['id']) || empty($args['id'])) {
            throw new Exception(__('Product ID was not provided', 'jigoshop'));
        }
        /** @var ProductService $service */
        $service = $this->app->getContainer()->di->get('jigoshop.service.product');
        $product = $service->find($args['id']);

        if(!$product instanceof ProductEntity) {
            throw new Exception(__('Product not found.', 'jigoshop'));
        }

        return $response->withJson([
            'success' => true,
            'data' => $this->getProductData($product),
        ]);
    }

    /**
     * @param ProductEntity $product
     * @return array
     */
    public function getProductData(ProductEntity $product)
    {
        return array_merge(
            $this->getBasicData($product),
            $this->getAttributes($product),
            $this->getCategories($product),
            $this->getTags($product)
        );
    }

    /**
     * @param ProductEntity $product
     * @return array
     */
    private function getBasicData(ProductEntity $product)
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
            'tax_classes' => $product->getTaxClasses(),
            'size' => array(
                'height' => $product->getSize()->getHeight(),
                'length' => $product->getSize()->getLength(),
                'width' => $product->getSize()->getWidth(),
                'weight' => $product->getSize()->getWeight()
            ),
            'link' => $product->getLink(),
        );
        if($product instanceof ProductEntity\Simple || $product instanceof ProductEntity\Downloadable || $product instanceof ProductEntity\External) {
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
        if($product instanceof ProductEntity\Downloadable) {
            $data = array_merge($data, array(
                'url' => $product->getUrl(),
                'download_limit' => $product->getLimit(),
            ));
        }
        if($product instanceof ProductEntity\External) {
            $data = array_merge($data, array(
                'url' => $product->getUrl()
            ));
        }
        if($product instanceof ProductEntity\Variable) {
            $data = array_merge($data, array(
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
                'lowest_price' => $product->getLowestPrice(),
                'highest_price' => $product->getHighestPrice(),
                'variations' => array_values(array_map(function($variation) {
                    /** @var ProductEntity\Variable\Variation $variation*/
                    return array(
                        'id' => $variation->getId(),
                        'name' => $variation->getTitle(),
                        'attributes' => array_values(array_map(function($attribute) {
                            /** @var ProductEntity\Variable\Attribute $attribute */
                            return array(
                                'slug' => $attribute->getAttribute()->getSlug(),
                                'value' => $attribute->getValue()
                            );
                        }, $variation->getAttributes())),
                    );
                }, $product->getVariations())),
            ));
        }
        return $data;
    }

    /**
     * @param $product ProductEntity
     *
     * @return array
     */
    public static function getAttributes($product)
    {
        return array(
            'attributes' => array_values(array_map(function($attribute) {
                /** @var ProductEntity\Attribute $attribute */
                return array(
                    'id' => $attribute->getId(),
                    'slug' => $attribute->getSlug(),
                    'type' => $attribute->getType(),
                    'label' => $attribute->getLabel(),
                    'options' => array_values(array_map(function($option) {
                        /** @var ProductEntity\Attribute\Option $option */
                        return array(
                            'id' => $option->getId(),
                            'label' => $option->getLabel(),
                            'value' => $option->getValue(),
                        );
                    }, $attribute->getOptions())),
                );
            }, $product->getAttributes())),
        );
    }
    /**
     * @param $product ProductEntity
     *
     * @return array
     */
    public static function getCategories($product)
    {
        return array(
            'categories' => array_values(array_map(function($category) {
                return array(
                    'id' => $category['id'],
                    'name' => $category['name'],
                    'slug' => $category['slug'],
                    'link' => $category['link'],
                );
            }, $product->getCategories())),
        );
    }
    /**
     * @param $product ProductEntity
     *
     * @return array
     */
    public static function getTags($product)
    {
        return array(
            'tags' => array_values(array_map(function($tag) {
                return array(
                    'id' => $tag['id'],
                    'name' => $tag['name'],
                    'slug' => $tag['slug'],
                    'link' => $tag['link'],
                );
            }, $product->getTags())),
        );
    }
    /**
     * @param $product ProductEntity
     *
     * @return array
     */
    public static function getAttachments($product)
    {
        $attachments = array();
        $types = array_unique(array_map(function($attachment) {
            return $attachment['type'];
        }, $product->getAttachments()));
        $uploadDir = wp_upload_dir(null, false);
        $uploadDir = $uploadDir['baseurl'];
        foreach($types as $type) {
            $attachments[$type] = array_values(array_map(function($attachment) use ($uploadDir) {
                $meta = get_post_meta($attachment['id'], '_wp_attachment_metadata', true);
                $meta['file'] = $uploadDir . '/' . $meta['file'];
                if(isset($meta['sizes'])) {
                    $meta['sizes'] = array_map(function($size) use ($uploadDir) {
                        $size['file'] = $uploadDir . '/' . $size['file'];
                        return $size;
                    }, $meta['sizes']);
                }
                return $meta;
            }, array_filter($product->getAttachments(), function($attachment) use ($type) {
                return $attachment['type'] == $type;
            })));
        }
        return array(
            'attachments' => $attachments,
        );
    }
}