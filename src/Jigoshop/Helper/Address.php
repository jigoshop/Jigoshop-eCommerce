<?php

namespace Jigoshop\Helper;

use Jigoshop\Core\Options as CoreOptions;

/**
 * Address helper.
 *
 * @package Jigoshop\Helper
 */
class Address
{
	/** @var Options */
	private static $options;

	/**
	 * @param CoreOptions $options Options object.
	 */
	public static function setOptions($options)
	{
		static::$options = $options;
	}

	/**
	 * Returns basic country, which is set in the store.
	 *
	 * @return string
	 */
	public static function getDefaultCountry()
	{
		return static::$options->get('general.country');
	}
}
