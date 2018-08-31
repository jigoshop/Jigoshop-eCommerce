<?php

namespace Jigoshop\Helper;

use Jigoshop\Core\Options as CoreOptions;
use Jigoshop\Entity\Customer\CompanyAddress;

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

    /**
     * Returns basic state, which is set in the store.
     *
     * @return string
     */
    public static function getDefaultState()
    {
        return static::$options->get('general.state');
	}

	/**
	 * Converts Address to CompanyAddress.
	 * 
	 * @param \Jigoshop\Entity\Customer\Address $customerAddress Customer address to convert.
	 *
	 * @return \Jigoshop\Entity\Customer\CompanyAddress Company address.
	 */
	public static function convertToCompanyAddress($customerAddress) {
		$companyAddress = new CompanyAddress();
		$companyAddress->setFirstName($customerAddress->getFirstName());
		$companyAddress->setLastName($customerAddress->getLastName());
		$companyAddress->setAddress($customerAddress->getAddress());
		$companyAddress->setCity($customerAddress->getCity());
		$companyAddress->setPostcode($customerAddress->getPostcode());
		$companyAddress->setCountry($customerAddress->getCountry());
		$companyAddress->setState($customerAddress->getState());
		$companyAddress->setEmail($customerAddress->getEmail());
		$companyAddress->setPhone($customerAddress->getPhone());

		return $companyAddress;		
	}
}
