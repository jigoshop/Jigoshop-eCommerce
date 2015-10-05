<?php
/**
 * Created by PhpStorm.
 * User: Borbis Media
 * Date: 2015-08-10
 * Time: 13:19
 */

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
	private $compilerPass = array();

	/**
	 * @param CompilerPassInterface $compilerPass
	 */
	public function add(CompilerPassInterface $compilerPass)
	{
		$this->compilerPass[] = $compilerPass;
	}

	/**
	 * @return array
	 */
	public function get()
	{
		return $this->compilerPass;
	}

	/**
	 * @param Container $container
	 */
	public function compile(Container $container)
	{
		$compilerPass = $this->get();

		foreach($compilerPass as $pass){
			$pass->process($container);
		}
	}
}