<?php
namespace Jigoshop\Container\Compiler;

use Jigoshop\Container;

/**
 * Interface CompilrtPassInterface
 *
 * @package Jigoshop\Container\Compile
 * @author Krzysztof Kasowski
 */
interface CompilerPassInterface
{
	/**
	 * @param Container $container
	 *
	 * @api
	 */
	public function process(Container $container);
}