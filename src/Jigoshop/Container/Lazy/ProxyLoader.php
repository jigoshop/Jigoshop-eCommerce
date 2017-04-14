<?php

namespace Jigoshop\Container\Lazy;

/**
 * Class LazyProxyLoader
 *
 * @package Jigoshop\Container
 * @author  Krzysztof Kasowski
 */
class ProxyLoader
{
	/** @var obiect $instance */
	private $instance = null;
	/** @var string $class_name */
	private $className;
	/** @var $classParams array */
	private $classParams;

	/**
	 * @param string $className
	 * @param array  $classParams
	 */
	public function __construct($className, array $classParams)
	{
		$this->setClassName($className);
		$this->getClassParams($classParams);
	}

	/**
	 * @param string $className
	 */
	public function setClassName($className)
	{
		$this->className = $className;
	}

	/**
	 * @param array $params
	 */
	public function setClassParams($params)
	{
		$this->classParams = $params;
	}

	/**
	 * @param object $instance
	 */
	public function setClassInstance($instance)
	{
		$this->instance = $instance;
	}

	/**
	 * @return string
	 */
	public function getClassName()
	{
		return $this->className;
	}

	/**
	 * @return array
	 */
	public function getClassParams()
	{
		return $this->classParams;
	}

	/**
	 * @return obiect
	 */
	public function getInstance()
	{
		if ($this->instance == null) {
			$this->initInstance();
		}

		return $this->instance;
	}

	public function initInstance()
	{
		$className = $this->getClassName();
		$params = $this->getClassParams();

		if (empty($arguments)) {
			$instance = new $className;
		} else {
			$reflection = new \ReflectionClass($className);
			$instance = $reflection->newInstanceArgs($params);
		}

		$this->setClassInstance($instance);
	}

	/**
	 * @param string $name
	 * @param array  $params
	 *
	 * @return mixed
	 */
	public function __call($name, $params)
	{
		$instance = $this->getInstance();

		return call_user_func_array([$instance, $name], $params);
	}

	/**
	 * @param string $name
	 * @param mixed  $value
	 */
	public function __set($name, $value)
	{
		$instance = $this->getInstance();
		$instance->$name = $value;
	}

	/**
	 * @param string $name
	 *
	 * @return mixed
	 */
	public function __get($name)
	{
		$instance = $this->getInstance();

		return $instance->$name;
	}

	/**
	 * @param string $name
	 *
	 * @return bool
	 */
	public function __isset($name)
	{
		$instance = $this->getInstance();

		return isset($instance->$name);
	}

	/**
	 * @param string $name
	 */
	public function __unset($name)
	{
		$instance = $this->getInstance();
		unset($instance->getInstance()->$name);
	}
}