<?php

namespace Jigoshop;

use Jigoshop\Container\ClassLoader;
use Jigoshop\Container\Compiler;
use Jigoshop\Container\Factories;
use Jigoshop\Container\Services;
use Jigoshop\Container\Tags;
use Jigoshop\Container\Triggers;

/**
 * Jigoshop Container,
 *
 * @package Jigoshop
 * @author  Krzysztof Kasowski
 */
class Container
{
	/** @var ClassLoader */
	public $classLoader;
	/** @var Services */
	public $services;
	/** @var Tags */
	public $tags;
	/** @var Triggers */
	public $triggers;
	/** @var Factories */
	public $factories;
	/** @var Compiler */
	public $compiler;

	public function __construct()
	{
		$this->classLoader = new ClassLoader();
		$this->services = new Services();
		$this->tags = new Tags();
		$this->triggers = new Triggers();
		$this->factories = new Factories();
		$this->compiler = new Compiler();

		$this->services->set('service_container', $this);
	}

	/**
	 * @param string $key
	 *
	 * @return object
	 */
	public function get($key)
	{
		if (!$this->services->exists($key)) {
			if (!$this->services->detailsExists($key)) {
				throw new Exception(sprintf('Service "%s", does not exist.', $key));
			}

			if (!$this->classLoader->exists($key, $this->services->getClassName($key))) {
				throw new Exception(sprintf('Class %s does not exist.', $key));
			}

			$params = $this->initServiceParams($key);

			if ($this->factories->exists($key)) {
				$service = $this->getServiceFromFactory($key, $params);
				$this->services->set($key, $service);
			} else {
				$this->services->createInstance($key, $params);
			}

			$this->activateTrigger($key);
		}

		return $this->services->get($key);
	}

	/**
	 * @param string $key
	 *
	 * @throws Container\Exception
	 */
	public function getTaggedServices($key)
	{
		$services = array();
		if ($this->tags->exists($key)) {
			foreach ($this->tags->get($key) as $serviceKey) {
				$services[] = $this->get($serviceKey);
			}
		}
	}

	/**
	 * @param string $key
	 *
	 * @return array
	 */
	private function initServiceParams($key)
	{
		$initedParams = array();
		$params = $this->services->getParams($key);

		if(!empty($params)){
			foreach ($params as $param) {
				$initedParams[] = $this->get($param);
			}
		}

		return $initedParams;
	}

	/**
	 * @param string $key
	 */
	private function activateTrigger($key)
	{
		if (!$this->triggers->exists($key)) {
			return;
		}

		$triggers = $this->triggers->get($key);
		foreach ($triggers as $trigger) {
			$object = $this->get($trigger['instance']);
			$method = $trigger['method'];
			$params = array();
			foreach ($trigger['params'] as $param) {
				$params[] = $this->get($param);
			}

			$this->triggers->callMethod($object, $method, $params);
		}
	}

	private function getServiceFromFactory($key, $params)
	{
		$factoryData = $this->factories->get($key);

		$factory = $this->get($factoryData['instance']);
		$method = $factoryData['method'];

		$instance = $this->factories->getService($factory, $method, $params);

		return $instance;
	}
}
