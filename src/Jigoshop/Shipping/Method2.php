<?php
namespace Jigoshop\Shipping;

/**
 * Shipping method interface.
 *
 * @package Jigoshop\Shipping
 */
interface Method2 extends Method
{
	/**
	 * Whenever method was enabled by the user.
	 * 
	 * @return boolean Method enable state.
	 */
	public function isActive();

	/**
	 * Set method enable state.
	 * 
	 * @param boolean $state Method enable state.
	 * 
	 * @return array Method current settings (after enable state change).
	 */
	public function setActive($state);

	/**
	 * Whenever method was configured by the user (all required data was filled for current scenario).
	 * 
	 * @return boolean Method config state. 
	 */
	public function isConfigured();

	/**
	 * Whenever method has some sort of test mode.
	 * 
	 * @return boolean Method test mode presence. 
	 */
	public function hasTestMode();

	/**
	 * Whenever method test mode was enabled by the user.
	 * 
	 * @return boolean Method test mode state. 
	 */
	public function isTestModeEnabled();

	/**
	 * Set Method test mode state. 
	 * 
	 * @param boolean $state Method test mode state.
	 * 
	 * @return array Method current settings (after test mode state change).
	 */
	public function setTestMode($state);
}