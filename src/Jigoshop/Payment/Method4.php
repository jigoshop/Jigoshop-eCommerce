<?php
namespace Jigoshop\Payment;

use Jigoshop\Entity\Order;

/**
 * Payment method interface.
 *
 * @package Jigoshop\Payment
 */
interface Method4 extends Method3
{
	/**
	 * Returns processing fee set by admin (in percent, or absolute price).
	 * At this point, Payment method should have this value parsed and validated and return only valid value.
	 * 
	 * @return mixed Processing fee (string if set in percent, float if absolute price, or null/zero if not set.
	 */
	public function getProcessingFee();

	/**
	 * Calculates processing fee based on Order.
	 * 
	 * @param \Entity\Order $order Order to calculate processing fee for.
	 *
	 * @return float Calculated processing fee.
	 */
	public function calculateProcessingFee(Order $order);
}
