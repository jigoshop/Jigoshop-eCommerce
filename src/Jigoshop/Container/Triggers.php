<?php

namespace Jigoshop\Container;

/**
 * Class Triggers
 *
 * @package Jigoshop\Container
 *
 * @author  Krzysztof Kasowski
 */
class Triggers
{
	/** @var array */
	private $triggers;

	/**
	 * @param string $key
	 * @param string $instance
	 * @param string $method
	 * @param array  $params
	 */
	public function add($key, $instance, $method, array $params)
	{
		$this->triggers[$key][] = array('instance' => $instance, 'method' => $method, 'params' => $params);
	}

	/**
	 * @param string $key
	 *
	 * @return array
	 */
	public function get($key)
	{
		return $this->triggers[$key];
	}

	/**
	 * @param string $key
	 *
	 * @return bool
	 */
	public function exists($key)
	{
		return isset($this->triggers[$key]);
	}

	/**
	 * @param        $object
	 * @param string $methodName
	 * @param array  $params
	 *
	 * @return mixed
	 */
	public function callMethod($object, $methodName, $params)
	{
		return call_user_func_array(array($object, $methodName), $params);
	}
}