<?php

namespace Jigoshop\Shipping;

use Jigoshop\Entity\OrderInterface;

interface MultipleMethod extends Method3
{
	/**
	 * Returns list of available shipping rates.
	 * 
	 * @param OrderInterface $order
	 *
	 * @return array List of available shipping rates.
	 */
	public function getRates($order);

	/**
	 * @param $rate int Rate to use.
	 */
	public function setShippingRate($rate);

	/**
	 * @return int Currently used rate.
	 */
	public function getShippingRate();
}
