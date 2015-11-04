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
class SystemInfoConfiguration implements ConfigurationInterface
{
	/**
	 * @param Services $services
	 *
	 * @return mixed
	 */
	public function initServices(Services $services)
	{
		$services->setDatails('jigoshop.admin.system_info.system_status', 'Jigoshop\Admin\SystemInfo\SystemStatusTab', array(
			'wpal',
			'jigoshop.options'
		));
		$services->setDatails('jigoshop.admin.system_info.tools', 'Jigoshop\Admin\SystemInfo\ToolsTab', array(
			'wpal',
			'jigoshop.options'
		));
		$services->setDatails('jigoshop.admin.system_info.logs', 'Jigoshop\Admin\SystemInfo\LogsTab', array(
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
		$tags->add('jigoshop.admin.system_info.tab', 'jigoshop.admin.system_info.system_status');
		$tags->add('jigoshop.admin.system_info.tab', 'jigoshop.admin.system_info.tools');
		$tags->add('jigoshop.admin.system_info.tab', 'jigoshop.admin.system_info.logs');
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