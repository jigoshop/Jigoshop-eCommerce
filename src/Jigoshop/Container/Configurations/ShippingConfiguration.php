<?php

namespace Jigoshop\Container\Configurations;

use Jigoshop\Container\Services;
use Jigoshop\Container\Tags;
use Jigoshop\Container\Triggers;
use Jigoshop\Container\Factories;
use Jigoshop\Container\ClassLoader;

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
	public function initServices(Services $services)
	{
		$services->setDetails('jigoshop.shipping.flat_rate', 'Jigoshop\Shipping\FlatRate', array(
			'wpal',
			'jigoshop.options',
			'jigoshop.service.cart',
			'jigoshop.messages'
		));
		$services->setDetails('jigoshop.shipping.free_shipping', 'Jigoshop\Shipping\FreeShipping', array(
			'wpal',
			'jigoshop.options',
			'jigoshop.service.cart',
			'jigoshop.messages'
		));
		$services->setDetails('jigoshop.shipping.local_pickup', 'Jigoshop\Shipping\LocalPickup', array(
			'wpal',
			'jigoshop.options',
			'jigoshop.service.cart'
		));
	}

	/**
	 * @param Tags $tags
	 *
	 * @return mixed
	 */
	public function initTags(Tags $tags)
	{
		$tags->add('jigoshop.shipping.method', 'jigoshop.shipping.flat_rate');
		$tags->add('jigoshop.shipping.method', 'jigoshop.shipping.free_shipping');
		$tags->add('jigoshop.shipping.method', 'jigoshop.shipping.local_pickup');
	}

	/**
	 * @param Triggers $triggers
	 *
	 * @return mixed
	 */
	public function initTriggers(Triggers $triggers)
	{

	}

	/**
	 * @param Factories $factories
	 *
	 * @return mixed
	 */
	public function initFactories(Factories $factories)
	{

	}

	/**
	 * @param ClassLoader $classLoader
	 *
	 * @return mixed
	 */
	public function initClassLoader(ClassLoader $classLoader)
	{

	}
}