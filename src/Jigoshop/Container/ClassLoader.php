<?php

namespace Jigoshop\Container;

use Jigoshop\Exception;

/**
 * Class ClassLoader
 *
 * @package Jigoshop\Container
 * @author  Krzysztof Kasowski
 */
class ClassLoader
{
	/** @var array */
	private $paths = array();
	/** @var array */
	private $autoloadPaths = array();

	/**
	 * Register autoload method.
	 */
	public function __construct()
	{
		spl_autoload_register(array($this, 'autoload'));
	}

	/**
	 * @param string $key
	 * @param string $path
	 *
	 * @throws \Jigoshop\Exception
	 */
	public function set($key, $path)
	{
		if (isset($this->paths[$key])) {
			throw new Exception(sprintf('Path for %s is already mapped.', $key));
		}

		$this->pathMap[$key] = $path;
	}

	/**
	 * @param string $key
	 * @param string $path
	 *
	 * @throws \Jigoshop\Exception
	 */
	public function addAutoloadPath($key, $path)
	{
		if (isset($this->autoloadPaths[$key])) {
			throw new Exception(sprintf('Autoload path for %s is already set.', $key));
		}

		$this->autoloadPaths[$key] = $path;
	}

	/**
	 * @param $key
	 * @param $className
	 *
	 * @return bool
	 */
	public function exists($key, $className)
	{
		return (class_exists($className) || $this->loadFile($key));
	}

	/**
	 * @param string $key
	 *
	 * @returns boolean
	 */
	private function loadFile($key)
	{
		if (isset($this->paths[$key]) && file_exists($this->paths[$key])) {
			require_once($this->paths[$key]);

			return true;
		}

		return false;
	}

	/**
	 * @param string $className
	 */
	public function autoload($className)
	{
		$className = str_replace('\\', '/', $className);
		foreach ($this->autoloadPaths as $path) {
			$file = $path.'/'.$className.'.php';
			if (file_exists($file)) {
				require_once $file;
			}
		}
	}
}
