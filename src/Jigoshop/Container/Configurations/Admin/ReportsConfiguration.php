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
class ReportsConfiguration implements ConfigurationInterface
{
	/**
	 * @param Services $services
	 *
	 * @return mixed
	 */
	public function addServices(Services $services)
	{
		$services->setDetails('jigoshop.admin.reports.sales', 'Jigoshop\Admin\Reports\SalesTab', [
			'wpal',
			'jigoshop.options'
        ]);
		$services->setDetails('jigoshop.admin.reports.customers', 'Jigoshop\Admin\Reports\CustomersTab', [
			'wpal',
			'jigoshop.options',
			'jigoshop.service.order',
        ]);
		$services->setDetails('jigoshop.admin.reports.stock', 'Jigoshop\Admin\Reports\StockTab', [
			'wpal',
			'jigoshop.options'
        ]);
	}

	/**
	 * @param Tags $tags
	 *
	 * @return mixed
	 */
	public function addTags(Tags $tags)
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