<?php

namespace Jigoshop\Container\Configurations;

use Jigoshop\Container\Services;
use Jigoshop\Container\Tags;
use Jigoshop\Container\Triggers;
use Jigoshop\Container\Factories;

/**
 * Class PaymentConfiguration
 *
 * @package Jigoshop\Container\Configuration
 * @author  Krzysztof Kasowski
 */
class PaymentConfiguration implements ConfigurationInterface
{
	/**
	 * @param Services $services
	 *
	 * @return mixed
	 */
	public function addServices(Services $services)
	{
		$services->setDetails('jigoshop.payment.cheque', 'Jigoshop\Payment\Cheque', [
			'wpal',
			'jigoshop.options',
            'jigoshop.service.order'
        ]);
		$services->setDetails('jigoshop.payment.on_delivery', 'Jigoshop\Payment\OnDelivery', [
			'wpal',
			'jigoshop.options',
            'jigoshop.service.order'
        ]);
		$services->setDetails('jigoshop.payment.paypal', 'Jigoshop\Payment\PayPal', [
			'wpal',
			'di',
			'jigoshop.options',
			'jigoshop.messages',
        ]);
        $services->setDetails('jigoshop.endpoint.paypal', 'Jigoshop\Payment\PayPal', [
            'wpal',
            'di',
            'jigoshop.options',
            'jigoshop.messages',
        ]);
		$services->setDetails('jigoshop.payment.worldpay', 'Jigoshop\Payment\WorldPay', [
			'wpal',
			'di',
			'jigoshop.options',
			'jigoshop.messages',
		]);
		$services->setDetails('jigoshop.payment.bank_transfer', 'Jigoshop\Payment\BankTransfer', [
			'wpal',
			'jigoshop.options',
            'jigoshop.service.order'
        ]);
	}

	/**
	 * @param Tags $tags
	 *
	 * @return mixed
	 */
	public function addTags(Tags $tags)
	{
		$tags->add('jigoshop.payment.method', 'jigoshop.payment.cheque');
		$tags->add('jigoshop.payment.method', 'jigoshop.payment.on_delivery');
		$tags->add('jigoshop.payment.method', 'jigoshop.payment.paypal');
		$tags->add('jigoshop.payment.method', 'jigoshop.payment.worldpay');
		$tags->add('jigoshop.payment.method', 'jigoshop.payment.bank_transfer');
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