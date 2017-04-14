<?php

namespace Jigoshop\Admin\Settings;

use Jigoshop\Container;
use Jigoshop\Container\Compiler\CompilerPassInterface;
/**
 * Compiler pass for loading all post types
 *
 * @package Jigoshop\Core\Types
 * @author  Amadeusz Starzykiewicz
 */
class CompilerPass implements CompilerPassInterface
{
	/**
	 * Inject post types and taxonomies into Types instance.
	 *
	 * @param Container $container
	 *
	 * @api
	 */
	public function process(Container $container)
	{
		if (!$container->services->detailsExists('jigoshop.admin.settings')) {
			return;
		}

		$tabs = $container->tags->get('jigoshop.admin.settings.tab');
		foreach ($tabs as $tab) {
			$container->triggers->add('jigoshop.admin.settings', 'jigoshop.admin.settings', 'addTab', [$tab]);
		}
	}
}
