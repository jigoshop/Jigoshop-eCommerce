<?php

namespace Jigoshop\Shipping;

use Jigoshop\Container;
use Jigoshop\Container\Compiler\CompilerPassInterface;

/**
 * Compiler pass for loading all shipping methods
 *
 * @package Jigoshop\Shipping
 * @author  Amadeusz Starzykiewicz
 */
class CompilerPass implements CompilerPassInterface
{
	/**
	 * Inject shipping methods into shipping service.
	 *
	 * @param \Jigoshop\Container $container
	 *
	 * @api
	 */
	public function process(Container $container)
	{
		if (!$container->services->detailsExists('jigoshop.service.shipping')) {
			return;
		}

		$methods = $container->tags->get('jigoshop.shipping.method');
		foreach ($methods as $method) {
			$container->triggers->add('jigoshop.service.shipping', 'jigoshop.service.shipping', 'addMethod', [$method]);
		}
	}
}
