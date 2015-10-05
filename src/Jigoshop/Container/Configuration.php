<?php
/**
 * Created by PhpStorm.
 * User: Borbis Media
 * Date: 2015-08-07
 * Time: 09:19
 */

namespace Jigoshop\Container;

use Jigoshop\Container;
use Jigoshop\Container\Configuration\ConfigurationInterface;

class Configuration
{
	private $configurations;

	public function init(Container $container)
	{
		foreach($this->configurations as $configuration) {
			$instance = new $configuration();
			if($instance instanceof ConfigurationInterface)	{
				$instance->initClassLoader($container->classLoader);
				$instance->initServices($container->services);
				$instance->initTags($container->tags);
				$instance->initTriggers($container->triggers);
				$instance->initFactories($container->factories);
			}
		}
	}

	public function getConfigurations()
	{
		$configurations = array(
			'\Jigoshop\Container\Configuration\MainConfiguration',
			'\Jigoshop\Container\Configuration\PagesConfiguration',
			'\Jigoshop\Container\Configuration\AdminConfiguration',
			'\Jigoshop\Container\Configuration\PaymentConfiguration',
			'\Jigoshop\Container\Configuration\ServicesConfiguration',
			'\Jigoshop\Container\Configuration\ShippingConfiguration',
			'\Jigoshop\Container\Configuration\FactoriesConfiguration',
			'\Jigoshop\Container\Configuration\Admin\MigrationConfiguration',
			'\Jigoshop\Container\Configuration\Admin\PagesConfiguration',
			'\Jigoshop\Container\Configuration\Admin\SettingsConfiguration'
		);
		$configurations = apply_filters('jigoshop\container\configuration', $configurations);

		$this->configurations = $configurations;
	}
}
