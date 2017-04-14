<?php

namespace Jigoshop\Core\Installer;

use Jigoshop\Container;
use Jigoshop\Container\Compiler\CompilerPassInterface;

/**
 * Compiler pass for loading all installation requirements.
 *
 * @package Jigoshop\Core\Installer
 * @author  Amadeusz Starzykiewicz
 */
class CompilerPass implements CompilerPassInterface
{
	/**
	 * Inject post types and taxonomies into Types instance.
	 *
	 * @param \Jigoshop\Container $container
	 *
	 * @api
	 */
	public function process(Container $container)
	{
		if (!$container->services->detailsExists('jigoshop.installer')) {
			return;
		}

		$installers = $container->tags->get('jigoshop.installer');
		foreach ($installers as $installer) {
			$container->triggers->add('jigoshop.installer', 'jigoshop.installer', 'addInitializer', [$installer]);
		}
	}
}
