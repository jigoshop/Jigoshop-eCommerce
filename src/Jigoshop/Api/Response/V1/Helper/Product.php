<?php

namespace Jigoshop\Api\Response\V1\Helper;

use Jigoshop\Entity\Product as EntityProduct;

/**
 * Class Products
 * @author Krzysztof Kasowski
 */
class Product
{
    /**
     * @param EntityProduct $product
     *
     * @return array
     */
    public static function getBasicData($product)
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

        if($product instanceof EntityProduct\Simple || $product instanceof EntityProduct\Downloadable || $product instanceof EntityProduct\External) {
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

        if($product instanceof EntityProduct\Downloadable) {
            $data = array_merge($data, array(
                'url' => $product->getUrl(),
                'download_limit' => $product->getLimit(),
            ));
        }

        if($product instanceof EntityProduct\External) {
            $data = array_merge($data, array(
                'url' => $product->getUrl()
            ));
        }

        if($product instanceof EntityProduct\Variable) {
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
                    /** @var EntityProduct\Variable\Variation $variation*/
                    return array(
                        'id' => $variation->getId(),
                        'name' => $variation->getTitle(),
                        'attributes' => array_values(array_map(function($attribute) {
                            /** @var EntityProduct\Variable\Attribute $attribute */
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
     * @param $product EntityProduct
     *
     * @return array
     */
    public static function getAttributes($product)
    {
        return array(
            'attributes' => array_values(array_map(function($attribute) {
                /** @var EntityProduct\Attribute $attribute */
                return array(
                    'id' => $attribute->getId(),
                    'slug' => $attribute->getSlug(),
                    'type' => $attribute->getType(),
                    'label' => $attribute->getLabel(),
                    'options' => array_values(array_map(function($option) {
                        /** @var EntityProduct\Attribute\Option $option */
                        return array(
                            'id' => $option->getId(),
                            'label' => $option->getLabel(),
                            'value' => $option->getValue(),
                        );
                    }, $attribute->getOptions())),
                    'value' => $attribute->getValue(),
                );
            }, $product->getAttributes())),
        );
    }

    /**
     * @param $product EntityProduct
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
     * @param $product EntityProduct
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
     * @param $product EntityProduct
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