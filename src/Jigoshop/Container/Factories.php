<?php

namespace Jigoshop\Container;

/**
 * Class Factories
 *
 * @package Jigoshop\Container
 * @author  Krzysztof Kasowski
 */
class Factories
{
	/** @var array $factories */
	private $factories = [];

	/**
	 * @param string $key
	 * @param string $instance
	 * @param string $methodName
	 */
	public function set($key, $instance, $methodName)
	{
		$this->factories[$key] = [
			'instance' => $instance,
			'method' => $methodName,
        ];
	}

	/**
	 * @param string $key
	 *
	 * @return array
	 */
	public function get($key)
	{
		return $this->factories[$key];
	}

	/**
	 * @param string $key
	 *
	 * @return bool
	 */
	public function exists($key)
	{
		return isset($this->factories[$key]);
	}

	/**
	 * @param string $instance
	 * @param string $method
	 * @param array  $params
	 *
	 * @return object
	 */
	public function getService($instance, $method, $params)
	{
		return call_user_func_array([$instance, $method], $params);
	}
}