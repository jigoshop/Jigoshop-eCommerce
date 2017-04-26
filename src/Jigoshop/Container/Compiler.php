<?php

namespace Jigoshop\Container;

use Jigoshop\Container;
use Jigoshop\Container\Compiler\CompilerPassInterface;

/**
 * Class Compiler
 *
 * @package Jigoshop\Container
 */
class Compiler
{
	/**
	 * @var array
	 */
	private $compilerPasses = [];

	/**
	 * @param CompilerPassInterface $compilerPass
	 */
	public function add(CompilerPassInterface $compilerPass)
	{
		$this->compilerPasses[] = $compilerPass;
	}

	/**
	 * @return CompilerPassInterface[]
	 */
	public function getAll()
	{
		return $this->compilerPasses;
	}
}