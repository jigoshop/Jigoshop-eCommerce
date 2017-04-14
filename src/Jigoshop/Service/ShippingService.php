<?php

namespace Jigoshop\Service;

use Jigoshop\Entity\Cart;
use Jigoshop\Exception;
use Jigoshop\Shipping\Dummy;
use Jigoshop\Shipping\Method;
use Monolog\Registry;

/**
 * Service for managing shipping methods.
 *
 * @package Jigoshop\Service
 */
class ShippingService implements ShippingServiceInterface
{
	private $methods = [];

	/**
	 * Adds new method to service.
	 *
	 * @param Method $method Method to add.
	 */
	public function addMethod(Method $method)
	{
		$this->methods[$method->getId()] = $method;
	}

	/**
	 * Finds item specified by state.
	 *
	 * @param array $state State of the method to be found.
	 *
	 * @return Method Method found.
	 */
	public function findForState(array $state)
	{
		$method = $this->get($state['id']);
		$method->restoreState($state);

		return $method;
	}

	/**
	 * Returns method by its ID.
	 *
	 * @param $id string ID of method.
	 *
	 * @return Method Method found.
	 * @throws Exception When no method is found for specified ID.
	 */
	public function get($id)
	{
		if (!isset($this->methods[$id])) {
			return new Dummy($id);
		}

		return $this->methods[$id];
	}

	/**
	 * Finds and returns ID of cheapest available shipping method.
	 *
	 * @param \Jigoshop\Entity\Cart $cart Cart to calculate method prices for.
	 *
	 * @return Method cheapest shipping method.
	 */
	public function getCheapest(Cart $cart)
	{
		$cheapest = null;
		$cheapestPrice = PHP_INT_MAX;

		foreach ($this->getEnabled() as $method) {
			/** @var Method $method */
			try {
				$price = $method->calculate($cart);

				if ($price < $cheapestPrice) {
					$cheapest = $method;
				}
			} catch(\Exception $e) {
				//Silence.....
			}
		}

		return $cheapest;
	}

	/**
	 * Returns list of enabled shipping methods.
	 *
	 * @return Method[] List of enabled shipping methods.
	 */
	public function getEnabled()
	{
		return array_filter($this->methods, function ($method){
			/** @var $method Method */
			return $method->isEnabled();
		});
	}

	/**
	 * Returns list of available shipping methods.
	 *
	 * @return Method[] List of available shipping methods.
	 */
	public function getAvailable()
	{
		return $this->methods;
	}
}
