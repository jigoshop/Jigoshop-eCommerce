<?php

namespace Jigoshop\Admin\SystemInfo;

use Jigoshop\Container;
use Jigoshop\Container\Compiler\CompilerPassInterface;

/**
 * Compiler pass for loading all post types
 *
 * @package Jigoshop\Admin\SystemInfo
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
		if (!$container->services->detailsExists('jigoshop.admin.system_info')) {
			return;
		}

		$tabs = $container->tags->get('jigoshop.admin.system_info.tab');
		foreach ($tabs as $tab) {
			$container->triggers->add('jigoshop.admin.system_info', 'jigoshop.admin.system_info', 'addTab', [$tab]);
		}
	}
}
