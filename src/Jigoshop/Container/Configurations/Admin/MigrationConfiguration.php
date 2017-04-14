<?php
namespace Jigoshop\Container\Configurations\Admin;

use Jigoshop\Container\Configurations\ConfigurationInterface;
use Jigoshop\Container\Services;
use Jigoshop\Container\Tags;
use Jigoshop\Container\Triggers;
use Jigoshop\Container\Factories;

/**
 * Class MigrationConfiguration
 *
 * @package Jigoshop\Container\Configuration
 * @author  Krzysztof Kasowski
 */
class MigrationConfiguration implements ConfigurationInterface
{
	/**
	 * @param Services $services
	 *
	 * @return mixed
	 */
	public function addServices(Services $services)
	{
		$services->setDetails('jigoshop.admin.migration.options', 'Jigoshop\Admin\Migration\Options', [
			'wpal',
			'jigoshop.options',
			'jigoshop.service.tax'
        ]);
		$services->setDetails('jigoshop.admin.migration.coupons', 'Jigoshop\Admin\Migration\Coupons', [
			'wpal',
			'jigoshop.options'
        ]);
		$services->setDetails('jigoshop.admin.migration.emails', 'Jigoshop\Admin\Migration\Emails', [
			'wpal',
			'jigoshop.options'
        ]);
		$services->setDetails('jigoshop.admin.migration.products', 'Jigoshop\Admin\Migration\Products', [
			'wpal',
			'jigoshop.options',
			'jigoshop.service.product',
			'jigoshop.service.tax'
        ]);
		$services->setDetails('jigoshop.admin.migration.orders', 'Jigoshop\Admin\Migration\Orders', [
			'wpal',
			'jigoshop.options',
			'jigoshop.messages',
			'jigoshop.service.order',
			'jigoshop.service.shipping',
			'jigoshop.service.payment',
			'jigoshop.service.product'
        ]);
	}

	/**
	 * @param Tags $tags
	 *
	 * @return mixed
	 */
	public function addTags(Tags $tags)
	{
		$tags->add('jigoshop.admin.migration', 'jigoshop.admin.migration.options');
		$tags->add('jigoshop.admin.migration', 'jigoshop.admin.migration.coupons');
		$tags->add('jigoshop.admin.migration', 'jigoshop.admin.migration.emails');
		$tags->add('jigoshop.admin.migration', 'jigoshop.admin.migration.products');
		$tags->add('jigoshop.admin.migration', 'jigoshop.admin.migration.orders');
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