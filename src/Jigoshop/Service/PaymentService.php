<?php

namespace Jigoshop\Service;

use Jigoshop\Exception;
use Jigoshop\Payment\Dummy;
use Jigoshop\Payment\Method;
use Monolog\Registry;

/**
 * Service for managing payment methods.
 *
 * @package Jigoshop\Service
 */
class PaymentService implements PaymentServiceInterface
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
			Registry::getInstance(JIGOSHOP_LOGGER)->addWarning(sprintf(__('Payment gateway "%s" does not exists', 'jigoshop-ecommerce'), $id));

			return new Dummy($id);
		}

		return $this->methods[$id];
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
}
