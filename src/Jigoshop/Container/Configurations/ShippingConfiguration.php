<?php

namespace Jigoshop\Container\Configurations;

use Jigoshop\Container\Services;
use Jigoshop\Container\Tags;
use Jigoshop\Container\Triggers;
use Jigoshop\Container\Factories;
/**
 * Class ShippingConfiguration
 *
 * @package Jigoshop\Container\Configuration
 * @author  Krzysztof Kasowski
 */
class ShippingConfiguration implements ConfigurationInterface
{
	/**
	 * @param Services $services
	 *
	 * @return mixed
	 */
	public function addServices(Services $services)
	{
		$services->setDetails('jigoshop.shipping.flat_rate', 'Jigoshop\Shipping\FlatRate', [
			'wpal',
			'jigoshop.options',
			'jigoshop.service.cart',
			'jigoshop.messages'
        ]);
		$services->setDetails('jigoshop.shipping.free_shipping', 'Jigoshop\Shipping\FreeShipping', [
			'wpal',
			'jigoshop.options',
			'jigoshop.service.cart',
			'jigoshop.service.order',
			'jigoshop.messages'
        ]);
		$services->setDetails('jigoshop.shipping.local_pickup', 'Jigoshop\Shipping\LocalPickup', [
			'wpal',
			'jigoshop.options',
			'jigoshop.service.cart'
        ]);
        $services->setDetails('jigoshop.shipping.advanced_flat_rate', 'Jigoshop\Shipping\AdvancedFlatRate', [
            'wpal',
            'jigoshop.options',
            'jigoshop.service.cart',
            'jigoshop.messages'
        ]);
	}

	/**
	 * @param Tags $tags
	 *
	 * @return mixed
	 */
	public function addTags(Tags $tags)
	{
		$tags->add('jigoshop.shipping.method', 'jigoshop.shipping.flat_rate');
		$tags->add('jigoshop.shipping.method', 'jigoshop.shipping.free_shipping');
		$tags->add('jigoshop.shipping.method', 'jigoshop.shipping.local_pickup');
		$tags->add('jigoshop.shipping.method', 'jigoshop.shipping.advanced_flat_rate');
	}

	/**
	 * @param Triggers $triggers
	 *
	 * @return mixed
	 */
	public function addTriggers(Triggers $triggers)
	{

	}

	/**
	 * @param Factories $factories
	 *
	 * @return mixed
	 */
	public function addFactories(Factories $factories)
	{

	}
}