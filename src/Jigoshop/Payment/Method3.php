<?php
namespace Jigoshop\Payment;

/**
 * Payment method interface.
 *
 * @package Jigoshop\Payment
 */
interface Method3 extends Method2
{
	/**
	 * Whenever method requires SSL to be enabled to function properly.
	 * 
	 * @return boolean Method SSL requirment.
	 */
	public function isSSLRequired();

	/**
	 * Whenever method is set to enabled for admin only.
	 * 
	 * @return boolean Method admin only state.
	 */
	public function isAdminOnly();

	/**
	 * Sets admin only state for the method and returns complete method options.
	 * 
	 * @param boolean $state Method admin only state.
	 * 
	 * @return array Complete method options after change was applied.
	 */
	public function setAdminOnly($state);
}
