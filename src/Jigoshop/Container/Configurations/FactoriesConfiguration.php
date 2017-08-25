<?php

namespace Jigoshop\Container\Configurations;

use Jigoshop\Container\Services;
use Jigoshop\Container\Tags;
use Jigoshop\Container\Triggers;
use Jigoshop\Container\Factories;

/**
 * Class FactoriesConfiguration
 *
 * @package Jigoshop\Container\Configuration
 * @author  Krzysztof Kasowski
 */
class FactoriesConfiguration implements ConfigurationInterface
{
	/**
	 * @param Services $services
	 *
	 * @return mixed
	 */
	public function addServices(Services $services)
	{
		$services->setDetails('jigoshop.factory.order_service', 'Jigoshop\Factory\OrderService', [
			'wpal',
			'jigoshop.options',
			'jigoshop.factory.order'
        ]);
		$services->setDetails('jigoshop.factory.product_service', 'Jigoshop\Factory\ProductService', [
			'wpal',
			'jigoshop.options',
			'jigoshop.factory.product'
        ]);
        $services->setDetails('jigoshop.factory.session_service', 'Jigoshop\Factory\SessionService', [
            'wpal',
            'jigoshop.options',
            'jigoshop.factory.session'
        ]);
		$services->setDetails('jigoshop.factory.variable_product_service', 'Jigoshop\Factory\Product\VariableService', [
			'wpal',
			'jigoshop.options',
			'jigoshop.factory.product.variable',
			'jigoshop.service.product'
        ]);
		$services->setDetails('jigoshop.factory.tax_service', 'Jigoshop\Factory\TaxService', [
			'wpal',
			'jigoshop.options',
			'jigoshop.service.customer'
        ]);
		$services->setDetails('jigoshop.factory.customer_service', 'Jigoshop\Factory\CustomerService', [
			'wpal',
			'jigoshop.options',
			'jigoshop.factory.customer',
            'jigoshop.service.session'
        ]);
		$services->setDetails('jigoshop.factory.product', 'Jigoshop\Factory\Product', [
			'wpal',
			'jigoshop.options'
        ]);
		$services->setDetails('jigoshop.factory.product.variable', 'Jigoshop\Factory\Product\Variable', [
			'wpal',
			'jigoshop.service.product'
        ]);
        $services->setDetails('jigoshop.factory.session', 'Jigoshop\Factory\Session', [
            'wpal',
            'jigoshop.options'
        ]);
		$services->setDetails('jigoshop.factory.order', 'Jigoshop\Factory\Order', [
			'wpal',
			'jigoshop.options',
			'jigoshop.messages'
        ]);
		$services->setDetails('jigoshop.factory.customer', 'Jigoshop\Factory\Customer', [
			'wpal',
            'jigoshop.service.session'
        ]);
		$services->setDetails('jigoshop.factory.shipping_service', 'Jigoshop\Factory\ShippingService', [
			'wpal',
			'jigoshop.options'
        ]);
		$services->setDetails('jigoshop.factory.payment_service', 'Jigoshop\Factory\PaymentService', [
			'wpal',
			'jigoshop.options'
        ]);
		$services->setDetails('jigoshop.factory.email', 'Jigoshop\Factory\Email', [
			'wpal',
			'jigoshop.options'
        ]);
		$services->setDetails('jigoshop.factory.coupon', 'Jigoshop\Factory\Coupon', [
			'wpal'
        ]);
        $services->setDetails('jigoshop.factory.product.category', 'Jigoshop\Factory\Product\Category', [
        	'wpal'
        ]);
        $services->setDetails('jigoshop.factory.product.category_service', 'Jigoshop\Factory\Product\CategoryService', [
        	'wpal',
        	'jigoshop.factory.product.category'
        ]);
        $services->setDetails('jigoshop.factory.cronjob', 'Jigoshop\Factory\Cronjob', [
        	'wpal'
        ]);
        $services->setDetails('jigoshop.factory.cronjob_service', 'Jigoshop\Factory\CronjobService', [
        	'wpal',
        	'jigoshop.factory.cronjob'
        ]);

		$services->setLazyStaus('jigoshop.factory.product', true);
		$services->setLazyStaus('jigoshop.factory.session', true);
		$services->setLazyStaus('jigoshop.factory.product.variable', true);
		$services->setLazyStaus('jigoshop.factory.order', true);
		$services->setLazyStaus('jigoshop.factory.customer', true);
		$services->setLazyStaus('jigoshop.factory.order', true);
		$services->setLazyStaus('jigoshop.factory.coupon', true);
		$services->setLazyStaus('jigoshop.factory.product.category', true);
		$services->setLazyStaus('jigoshop.factory.cronjob', true);
	}

	/**
	 * @param Tags $tags
	 *
	 * @return mixed
	 */
	public function addTags(Tags $tags)
	{

	}

	/**
	 * @param Triggers $triggers
	 *
	 * @return mixed
	 */
	public function addTriggers(Triggers $triggers)
	{
		$triggers->add('jigoshop.service.order', 'jigoshop.factory.order', 'init', [
			'jigoshop.service.customer',
			'jigoshop.service.product',
			'jigoshop.service.shipping',
			'jigoshop.service.payment',
			'jigoshop.service.coupon'
        ]);
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