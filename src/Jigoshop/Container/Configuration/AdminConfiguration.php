<?php
namespace Jigoshop\Container\Configuration;

use Jigoshop\Container\Services;
use Jigoshop\Container\Tags;
use Jigoshop\Container\Triggers;
use Jigoshop\Container\Factories;
use Jigoshop\Container\ClassLoader;

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
	public function initServices(Services $services)
	{
		$services->setDatails('jigoshop.admin', 'Jigoshop\Admin', array(
			'wpal',
			'jigoshop.admin.dashboard',
			'jigoshop.admin.permalinks'
		));
		$services->setDatails('jigoshop.admin.pages', 'Jigoshop\Admin\Pages', array(
			'wpal',
			'jigoshop.options'
		));
		$services->setDatails('jigoshop.admin.permalinks', 'Jigoshop\Admin\Permalinks', array(
			'wpal',
			'jigoshop.options'
		));
		$services->setDatails('jigoshop.admin.dashboard', 'Jigoshop\Admin\Dashboard', array(
			'wpal',
			'jigoshop.options',
			'jigoshop.service.order',
			'jigoshop.service.product'
		));
		$services->setDatails('jigoshop.admin.reports', 'Jigoshop\Admin\Reports', array(
			'wpal',
			'jigoshop.messages',
			'jigoshop.service.order'
		));
		$services->setDatails('jigoshop.admin.settings', 'Jigoshop\Admin\Settings', array(
			'wpal',
			'jigoshop.options',
			'jigoshop.messages'
		));
		$services->setDatails('jigoshop.admin.system_info', 'Jigoshop\Admin\SystemInfo', array(
			'wpal'
		));
		$services->setDatails('jigoshop.admin.licences', 'Jigoshop\Admin\Licences', array(
			'wpal',
			'jigoshop.options',
			'jigoshop.messages'
		));
		$services->setDatails('jigoshop.admin.migration', 'Jigoshop\Admin\Migration', array(
			'wpal',
			'jigoshop.options',
			'jigoshop.messages'
		));
		$services->setDatails('jigoshop.admin.product.attributes', 'Jigoshop\Admin\Product\Attributes', array(
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
	public function initTags(Tags $tags)
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