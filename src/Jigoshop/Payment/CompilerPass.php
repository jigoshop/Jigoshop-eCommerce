<?php

namespace Jigoshop\Payment;

use Jigoshop\Container;
use Jigoshop\Container\Compiler\CompilerPassInterface;

/**
 * Compiler pass for loading all shipping methods
 *
 * @package Jigoshop\Payment
 * @author  Amadeusz Starzykiewicz
 */
class CompilerPass implements CompilerPassInterface
{
	/**
	 * Inject payment methods into payment service.
	 *
	 * @param \Jigoshop\Container $container
	 *
	 * @api
	 */
	public function process(Container $container)
	{
		if (!$container->services->detailsExists('jigoshop.service.payment')) {
			return;
		}

		$methods = $container->tags->get('jigoshop.payment.method');
		foreach ($methods as $method) {
			$container->triggers->add('jigoshop.service.payment', 'jigoshop.service.payment', 'addMethod', [$method]);
		}
	}
}
