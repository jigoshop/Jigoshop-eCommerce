<?php
namespace Jigoshop\Container\Configurations\Admin;

use Jigoshop\Container\Configurations\ConfigurationInterface;
use Jigoshop\Container\Services;
use Jigoshop\Container\Tags;
use Jigoshop\Container\Triggers;
use Jigoshop\Container\Factories;

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
	public function addServices(Services $services)
	{
		$services->setDetails('jigoshop.admin.settings.general', 'Jigoshop\Admin\Settings\GeneralTab', [
			'wpal',
			'jigoshop.options',
			'jigoshop.messages'
        ]);
		$services->setDetails('jigoshop.admin.settings.shopping', 'Jigoshop\Admin\Settings\ShoppingTab', [
			'wpal',
			'jigoshop.options',
			'jigoshop.messages'
        ]);
		$services->setDetails('jigoshop.admin.settings.products', 'Jigoshop\Admin\Settings\ProductsTab', [
			'wpal',
			'jigoshop.options',
			'jigoshop.messages'
        ]);
		$services->setDetails('jigoshop.admin.settings.taxes', 'Jigoshop\Admin\Settings\TaxesTab', [
			'wpal',
			'jigoshop.options',
			'jigoshop.service.tax',
			'jigoshop.messages'
        ]);
		$services->setDetails('jigoshop.admin.settings.shipping', 'Jigoshop\Admin\Settings\ShippingTab', [
		    'wpal',
            'jigoshop.options',
            'jigoshop.service.shipping',
        ]);
		$services->setDetails('jigoshop.admin.settings.payment', 'Jigoshop\Admin\Settings\PaymentTab', [
			'wpal',
			'jigoshop.options',
			'jigoshop.service.payment',
			'jigoshop.messages'
        ]);
        $services->setDetails('jigoshop.admin.settings.layout', 'Jigoshop\Admin\Settings\LayoutTab', [
            'wpal',
            'jigoshop.options',
            'jigoshop.messages'
        ]);
		$services->setDetails('jigoshop.admin.settings.advanced', 'Jigoshop\Admin\Settings\AdvancedTab', [
			'wpal',
			'di',
			'jigoshop.options',
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
		$tags->add('jigoshop.admin.settings.tab', 'jigoshop.admin.settings.general');
		$tags->add('jigoshop.admin.settings.tab', 'jigoshop.admin.settings.shopping');
		$tags->add('jigoshop.admin.settings.tab', 'jigoshop.admin.settings.products');
		$tags->add('jigoshop.admin.settings.tab', 'jigoshop.admin.settings.taxes');
		$tags->add('jigoshop.admin.settings.tab', 'jigoshop.admin.settings.shipping');
		$tags->add('jigoshop.admin.settings.tab', 'jigoshop.admin.settings.payment');
		$tags->add('jigoshop.admin.settings.tab', 'jigoshop.admin.settings.layout');
		$tags->add('jigoshop.admin.settings.tab', 'jigoshop.admin.settings.advanced');
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