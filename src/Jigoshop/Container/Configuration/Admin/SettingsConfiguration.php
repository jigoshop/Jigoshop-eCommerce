<?php
namespace Jigoshop\Container\Configuration\Admin;

use Jigoshop\Container\Configuration\ConfigurationInterface;
use Jigoshop\Container\Services;
use Jigoshop\Container\Tags;
use Jigoshop\Container\Triggers;
use Jigoshop\Container\Factories;
use Jigoshop\Container\ClassLoader;

/**
 * Class SettingsConfiguration
 *
 * @package Jigoshop\Container\Configuration
 * @author  Krzysztof Kasowski
 */
class SettingsConfiguration implements ConfigurationInterface
{
	/**
	 * @param Services $services
	 *
	 * @return mixed
	 */
	public function initServices(Services $services)
	{
		$services->setDetails('jigoshop.admin.settings.general', 'Jigoshop\Admin\Settings\GeneralTab', array(
			'wpal',
			'jigoshop.options',
			'jigoshop.messages'
		));
		$services->setDetails('jigoshop.admin.settings.shopping', 'Jigoshop\Admin\Settings\ShoppingTab', array(
			'wpal',
			'jigoshop.options',
			'jigoshop.messages'
		));
		$services->setDetails('jigoshop.admin.settings.products', 'Jigoshop\Admin\Settings\ProductsTab', array(
			'wpal',
			'jigoshop.options',
			'jigoshop.messages'
		));
		$services->setDetails('jigoshop.admin.settings.taxes', 'Jigoshop\Admin\Settings\TaxesTab', array(
			'wpal',
			'jigoshop.options',
			'jigoshop.service.tax',
			'jigoshop.messages'
		));
		$services->setDetails('jigoshop.admin.settings.shipping', 'Jigoshop\Admin\Settings\ShippingTab', array(
            'jigoshop.options',
            'jigoshop.service.shipping',
		));
		$services->setDetails('jigoshop.admin.settings.payment', 'Jigoshop\Admin\Settings\PaymentTab', array(
			'jigoshop.options',
			'jigoshop.service.payment'
		));
		$services->setDetails('jigoshop.admin.settings.advanced', 'Jigoshop\Admin\Settings\AdvancedTab', array(
			'wpal',
			'service_container',
			'jigoshop.options',
			'jigoshop.messages'
		));
	}

	/**
	 * @param Tags $tags
	 *
	 * @return mixed
	 */
	public function initTags(Tags $tags)
	{
		$tags->add('jigoshop.admin.settings.tab', 'jigoshop.admin.settings.general');
		$tags->add('jigoshop.admin.settings.tab', 'jigoshop.admin.settings.shopping');
		$tags->add('jigoshop.admin.settings.tab', 'jigoshop.admin.settings.products');
		$tags->add('jigoshop.admin.settings.tab', 'jigoshop.admin.settings.taxes');
		$tags->add('jigoshop.admin.settings.tab', 'jigoshop.admin.settings.shipping');
		$tags->add('jigoshop.admin.settings.tab', 'jigoshop.admin.settings.payment');
		$tags->add('jigoshop.admin.settings.tab', 'jigoshop.admin.settings.advanced');
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