<?php

namespace Jigoshop\Api\Response\V1\Helper;

use Jigoshop\Entity\Order as OrderEntity;
use Jigoshop\Shipping\MultipleMethod;
use Jigoshop\Shipping\Rate;

/**
 * Class Order
 * @package Jigoshop\Api\Response\V1\Helper;
 * @author Krzysztof Kasowski
 */
class Order
{
    /**
     * @param OrderEntity $order
     *
     * @return array
     */
    public static function getBasicData($order)
    {
        $data = array(
            'id' => $order->getId(),
            'created_at' => array(
                'timestamp' => $order->getCreatedAt()->getTimestamp(),
                'date' => $order->getCreatedAt()->format('Y-m-d'),
            ),
            'number' => $order->getNumber(),
            'status' => $order->getStatus(),
            'customer' => array(),
            'customers_note' => $order->getCustomerNote(),
            'items' => array_values(array_map(function($item) {
                /** @var OrderEntity\Item $item */
                return array(
                    'id' => $item->getId(),
                    'type' => $item->getType(),
                    'key' => $item->getKey(),
                    'name' => $item->getName(),
                    'product_id' => $item->getProductId(),
                    'meta' => array_values(array_map(function($meta) {
                        /** @var OrderEntity\Item\Meta $meta */
                        return array(
                            'key' => $meta->getKey(),
                            'value' => $meta->getValue(),
                        );
                    }, $item->getAllMeta())),
                    'price' => $item->getPrice(),
                    'quantity' => $item->getQuantity(),
                    'tax' => $item->getTax(),
                    'tax_classes' => $item->getTaxClasses(),
                    'cost' => $item->getCost(),
                );
            }, $order->getItems())),
            'products_subtotal' => $order->getProductSubtotal(),
            'shipping_price' => array(
                'method' => array(
                    'id' => $order->getShippingMethod()->getId(),
                    'title' => $order->getShippingMethod()->getTitle(),
                ),
                'tax' => $order->getShippingTax(),
                'price' => $order->getShippingPrice(),
            ),
            'subtotal' => $order->getSubtotal(),
            'tax' => $order->getTax(),
            'total' => $order->getTotal(),
        );
        if($order->getShippingMethod() instanceof  MultipleMethod) {
            $rates = $order->getShippingMethod()->getRates($order);
            $rateId = $order->getShippingMethod()->getShippingRate();
            /** @var Rate $rate */
            $rate = isset($rates[$rateId]) ? $rates[$rateId] : null;
            if($rate) {
                $data['shipping_price']['method']['rate'] = array(
                    'id' => $rate->getId(),
                    'title' => $rate->getName(),
                );
            }
        }

        return $data;
    }
}