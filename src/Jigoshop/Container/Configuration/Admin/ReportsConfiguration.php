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
class ReportsConfiguration implements ConfigurationInterface
{
	/**
	 * @param Services $services
	 *
	 * @return mixed
	 */
	public function initServices(Services $services)
	{
		$services->setDatails('jigoshop.admin.reports.sales', 'Jigoshop\Admin\Reports\SalesTab', array(
			'wpal',
			'jigoshop.options'
		));
		$services->setDatails('jigoshop.admin.reports.customers', 'Jigoshop\Admin\Reports\CustomersTab', array(
			'wpal',
			'jigoshop.options',
			'jigoshop.service.order',
		));
		$services->setDatails('jigoshop.admin.reports.stock', 'Jigoshop\Admin\Reports\StockTab', array(
			'wpal',
			'jigoshop.options'
		));
	}

	/**
	 * @param Tags $tags
	 *
	 * @return mixed
	 */
	public function initTags(Tags $tags)
	{
		$tags->add('jigoshop.admin.reports.tab', 'jigoshop.admin.reports.sales');
		$tags->add('jigoshop.admin.reports.tab', 'jigoshop.admin.reports.customers');
		$tags->add('jigoshop.admin.reports.tab', 'jigoshop.admin.reports.stock');
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