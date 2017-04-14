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
class SystemInfoConfiguration implements ConfigurationInterface
{
	/**
	 * @param Services $services
	 *
	 * @return mixed
	 */
	public function addServices(Services $services)
	{
		$services->setDetails('jigoshop.admin.system_info.system_status', 'Jigoshop\Admin\SystemInfo\SystemStatusTab', [
			'wpal',
			'jigoshop.options'
        ]);
		$services->setDetails('jigoshop.admin.system_info.tools', 'Jigoshop\Admin\SystemInfo\ToolsTab', [
			'wpal',
			'jigoshop.options'
        ]);
		$services->setDetails('jigoshop.admin.system_info.logs', 'Jigoshop\Admin\SystemInfo\LogsTab', []);
	}

	/**
	 * @param Tags $tags
	 *
	 * @return mixed
	 */
	public function addTags(Tags $tags)
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