<?php

namespace Jigoshop\Admin\Migration;

use Jigoshop\Container;
use Jigoshop\Container\Compiler\CompilerPassInterface;


/**
 * Compiler pass for loading all migration tools
 *
 * @package Jigoshop\Admin\Migration
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
		if (!$container->services->detailsExists('jigoshop.admin.migration')) {
			return;
		}

		$tools = $container->tags->get('jigoshop.admin.migration');
		foreach ($tools as $tool) {
			$container->triggers->add('jigoshop.admin.migration', 'jigoshop.admin.migration', 'addTool', [$tool]);
		}
	}
}
