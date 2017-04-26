<?php

namespace Jigoshop\Admin;

use Jigoshop\Container;
use Jigoshop\Container\Compiler\CompilerPassInterface;

/**
 * @package Jigoshop\Admin
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
		if (!$container->services->detailsExists('jigoshop.admin')) {
			return;
		}

		$pages = $container->tags->get('jigoshop.admin.page');
		foreach ($pages as $page)
		{
			if (!\Jigoshop\Helper\Migration::needMigrationTool() && $page == 'jigoshop.admin.migration')
			{
				continue;
			}

			$container->triggers->add('jigoshop.admin', 'jigoshop.admin', 'addPage', [$page]);
		}
	}
}
