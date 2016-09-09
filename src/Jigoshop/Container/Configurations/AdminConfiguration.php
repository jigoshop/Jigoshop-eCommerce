<?php
namespace Jigoshop\Container\Configurations;

use Jigoshop\Container\Services;
use Jigoshop\Container\Tags;
use Jigoshop\Container\Triggers;
use Jigoshop\Container\Factories;
/**
 * Class AdminConfiguration
 *
 * @package Jigoshop\Container\Configuration
 * @author  Krzysztof Kasowski
 */
class AdminConfiguration implements ConfigurationInterface
{
	/**
	 * @param Services $services
	 *
	 * @return mixed
	 */
	public function addServices(Services $services)
	{
		$services->setDetails('jigoshop.admin', 'Jigoshop\Admin', array(
			'wpal',
			'jigoshop.admin.dashboard',
			'jigoshop.admin.permalinks'
		));
		$services->setDetails('jigoshop.admin.pages', 'Jigoshop\Admin\Pages', array(
			'wpal',
			'jigoshop.options'
		));
		$services->setDetails('jigoshop.admin.permalinks', 'Jigoshop\Admin\Permalinks', array(
			'wpal',
			'jigoshop.options'
		));
		$services->setDetails('jigoshop.admin.dashboard', 'Jigoshop\Admin\Dashboard', array(
			'wpal',
			'jigoshop.options',
			'jigoshop.service.order',
			'jigoshop.service.product'
		));
		$services->setDetails('jigoshop.admin.reports', 'Jigoshop\Admin\Reports', array(
			'wpal',
			'jigoshop.messages',
			'jigoshop.service.order'
		));
		$services->setDetails('jigoshop.admin.settings', 'Jigoshop\Admin\Settings', array(
			'wpal',
			'jigoshop.options',
			'jigoshop.messages'
		));
		$services->setDetails('jigoshop.admin.system_info', 'Jigoshop\Admin\SystemInfo', array(
			'wpal'
		));
		$services->setDetails('jigoshop.admin.licences', 'Jigoshop\Admin\Licences', array(
			'wpal',
			'jigoshop.options',
			'jigoshop.messages'
		));
		$services->setDetails('jigoshop.admin.migration', 'Jigoshop\Admin\Migration', array(
			'wpal',
			'jigoshop.options',
			'jigoshop.messages'
		));
		$services->setDetails('jigoshop.admin.product.attributes', 'Jigoshop\Admin\Product\Attributes', array(
			'wpal',
			'jigoshop.messages',
			'jigoshop.service.product'
		));
	}

	/**
	 * @param Tags $tags
	 *
	 * @return mixed
	 */
	public function addTags(Tags $tags)
	{
		$tags->add('jigoshop.admin.page', 'jigoshop.admin.dashboard');
		$tags->add('jigoshop.admin.page', 'jigoshop.admin.reports');
		$tags->add('jigoshop.admin.page', 'jigoshop.admin.settings');
		$tags->add('jigoshop.admin.page', 'jigoshop.admin.system_info');
		$tags->add('jigoshop.admin.page', 'jigoshop.admin.licences');
		$tags->add('jigoshop.admin.page', 'jigoshop.admin.migration');
		$tags->add('jigoshop.admin.page', 'jigoshop.admin.product.attributes');
	}

	/**
	 * @param Triggers $triggers
	 *
	 * @return mixed
	 */
	public function addTriggers(Triggers $triggers)
	{
        $triggers->add('jigoshop.admin', 'jigoshop.admin.page_resolver', 'resolve', array('di'));
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