<?php

namespace Jigoshop\Container\Configurations;

use Jigoshop\Container\Services;
use Jigoshop\Container\Tags;
use Jigoshop\Container\Triggers;
use Jigoshop\Container\Factories;

/**
 * Class ServicesConfiguration
 *
 * @package Jigoshop\Container\Configuration
 * @author  Krzysztof Kasowski
 */
class ServicesConfiguration implements ConfigurationInterface
{
	/**
	 * @param Services $services
	 *
	 * @return mixed
	 */
	public function addServices(Services $services)
	{
		$services->setDetails('jigoshop.service.order', 'Jigoshop\Service\OrderService', []);
		$services->setDetails('jigoshop.service.product', 'Jigoshop\Service\ProductService', []);
		$services->setDetails('jigoshop.service.product.variable', 'Jigoshop\Service\Product\VariableService', []);
		$services->setDetails('jigoshop.service.tax', 'Jigoshop\Service\TaxService', []);
		$services->setDetails('jigoshop.service.customer', 'Jigoshop\Service\CustomerService', []);
		$services->setDetails('jigoshop.service.shipping', 'Jigoshop\Service\ShippingService', []);
		$services->setDetails('jigoshop.service.payment', 'Jigoshop\Service\PaymentService', []);
        $services->setDetails('jigoshop.service.session', 'Jigoshop\Service\SessionService', []);
		$services->setDetails('jigoshop.service.cart', 'Jigoshop\Service\CartService', [
			'wpal',
			'jigoshop.options',
			'jigoshop.service.customer',
			'jigoshop.service.product',
			'jigoshop.service.shipping',
			'jigoshop.service.session',
			'jigoshop.service.payment',
			'jigoshop.factory.order'
        ]);
		$services->setDetails('jigoshop.service.email', 'Jigoshop\Service\EmailService', [
			'wpal',
			'jigoshop.options',
			'jigoshop.factory.email'
        ]);
		$services->setDetails('jigoshop.service.coupon', 'Jigoshop\Service\CouponService', [
			'wpal',
			'jigoshop.options',
			'jigoshop.factory.coupon'
        ]);
        $services->setDetails('jigoshop.service.product.category', 'Jigoshop\Service\Product\CategoryService', [
        	'wpal',
        	'jigoshop.factory.product.category'
        ]);
        $services->setDetails('jigoshop.service.cron', 'Jigoshop\Service\CronService', [
        	'wpal',
        	'jigoshop.factory.cronjob'
        ]);

		$services->setLazyStaus('jigoshop.service.order', true);
		$services->setLazyStaus('jigoshop.service.product', true);
		$services->setLazyStaus('jigoshop.service.product.variable', true);
		$services->setLazyStaus('jigoshop.service.tax', true);
		$services->setLazyStaus('jigoshop.service.customer', true);
		$services->setLazyStaus('jigoshop.service.shipping', true);
		$services->setLazyStaus('jigoshop.service.payment', true);
		$services->setLazyStaus('jigoshop.service.session', true);
		$services->setLazyStaus('jigoshop.service.cart', true);
		$services->setLazyStaus('jigoshop.service.coupon', true);
		$services->setLazyStaus('jigoshop.service.product.category', true);
		$services->setLazyStaus('jigoshop.service.cron', true);
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
		$triggers->add('jigoshop.service.cart', 'jigoshop.service.cart', 'init', []);
	}

	/**
	 * @param Factories $factories
	 *
	 * @return mixed
	 */
	public function addFactories(Factories $factories)
	{
		$factories->set('jigoshop.service.order', 'jigoshop.factory.order_service', 'getService');
		$factories->set('jigoshop.service.product', 'jigoshop.factory.product_service', 'getService');
		$factories->set('jigoshop.service.product.variable', 'jigoshop.factory.variable_product_service', 'getService');
		$factories->set('jigoshop.service.tax', 'jigoshop.factory.tax_service', 'getService');
		$factories->set('jigoshop.service.customer', 'jigoshop.factory.customer_service', 'getService');
		$factories->set('jigoshop.service.shipping', 'jigoshop.factory.shipping_service', 'getService');
		$factories->set('jigoshop.service.payment', 'jigoshop.factory.payment_service', 'getService');
		$factories->set('jigoshop.service.session', 'jigoshop.factory.session_service', 'getService');
		$factories->set('jigoshop.service.product.category', 'jigoshop.factory.product.category_service', 'getService');
		$factories->set('jigoshop.service.cron', 'jigoshop.factory.cronjob_service', 'getService');
	}
}