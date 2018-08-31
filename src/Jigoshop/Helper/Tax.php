<?php

namespace Jigoshop\Helper;

use Jigoshop\Entity\OrderInterface;
use Jigoshop\Entity\Product as ProductEntity;
use Jigoshop\Exception;
use Jigoshop\Helper\Country;
use Jigoshop\Service\CartServiceInterface;
use Jigoshop\Service\TaxServiceInterface;

class Tax
{
    const EU_VAT_VALIDATION_RESULT_VALID = 'valid';
    const EU_VAT_VALIDATION_RESULT_INVALID = 'invalid';
    const EU_VAT_VALIDATION_RESULT_ERROR = 'error';

	/** @var TaxServiceInterface */
	private static $taxService;
	/** @var  CartServiceInterface */
	private static $cartService;

	public static function setTaxService(TaxServiceInterface $taxService)
	{
		self::$taxService = $taxService;
	}

    /**
     * @param CartServiceInterface $cartService
     */
    public static function setCartService($cartService)
    {
        self::$cartService = $cartService;
    }
	/**
	 * Returns proper tax label if tax service is running.
	 *
	 * @param                $taxClass string Tax class.
	 * @param OrderInterface $order    Order to calculate taxes for.
	 *
	 * @return string Tax label.
	 */
	public static function getLabel($taxClass, $order)
	{
		if (self::$taxService !== null) {
			return self::$taxService->getLabel($taxClass, $order);
		}

		return $taxClass;
	}

	/**
	 * Returns proper tax rate if tax service is running.
	 *
	 * @param                $taxClass string Tax class.
	 * @param OrderInterface $order    Order to calculate taxes for.
	 *
	 * @return float Tax rate.
	 */
	public static function getRate($taxClass, $order)
	{
		if (self::$taxService !== null) {
			return self::$taxService->getRate($taxClass, $order);
		}

		return 0;
	}

    public static function getPriceWithoutTax($price, $taxClasses, $order = null)
    {
        if($order instanceof OrderInterface || self::$cartService) {
            $taxDefinitions = $order instanceof OrderInterface ? $order->getTaxDefinitions() : self::$cartService->getCurrent()->getTaxDefinitions();
            $standard = $compound = [];
            foreach ($taxClasses as $class) {
                if(isset($taxDefinitions[$class])) {
                    $standard[$class] = $taxDefinitions[$class];
                    if (isset($taxDefinitions['__compound__' . $class])) {
                        $compound[$class] = $taxDefinitions['__compound__' . $class];
                    }
                }
            }

            $standardRate = 0;
            foreach ($standard as $class => $definition) {
                $standardRate += $definition['rate'] / 100;
            }
            $compoundRate = 0;
            foreach ($compound as $class => $definition) {
                $compoundRate += $definition['rate'] / 100;
            }

            if($price === '') {
                $price = 0;
            }

            $price = ($price/((1 + $standardRate) * (1 + $compoundRate)));
        }

        return $price;
	}
    /**
     * @param $price
     * @param ProductEntity $product
     * @return float|int
     */
    public static function getForProduct($price, ProductEntity $product)
    {
        if($product instanceof ProductEntity\Taxable && self::$cartService) {
            return self::getTax($price, $product->getTaxClasses(), self::$cartService->getCurrent()->getTaxDefinitions());
        }

        return 0;
	}

    /**
     * @param $price
     * @param array $taxClasses
     * @param array $definitions
     *
     * @return float|int
     */
    public static function getTax($price, array $taxClasses, array $definitions)
    {
        if (self::$taxService) {
            return array_sum(self::$taxService->get($price, $taxClasses, $definitions));
        }

        return 0;
	}

    /**
     * Checks if supplied EU VAT number is valid.
     * 
     * @param string $euVatNumber EU VAT number to verify.
     * @param string $billingCountryCode Country code of billing address country.
     * 
     * @return string Validation result (\Jigoshop\Helper\Tax::EU_VAT_VALIDATION_RESULT_*).
     */
    public static function validateEUVatNumber($euVatNumber, $billingCountryCode) {
        if(strlen($euVatNumber) < 2) {
            return self::EU_VAT_VALIDATION_RESULT_INVALID;
        }

        $euVatNumber = strtoupper($euVatNumber);

        $memberCountry = substr($euVatNumber, 0, 2);
        if(!Country::isEU($memberCountry)) {
            return self::EU_VAT_VALIDATION_RESULT_INVALID;
        }

        $cache = get_transient('jigoshop_euvat');
        if(!is_array($cache)) {
            $cache = [];
        }

        if(isset($cache[$euVatNumber]) && $cache[$euVatNumber] >= time()) {
            return self::EU_VAT_VALIDATION_RESULT_VALID;
        }

        $vatNumber = substr($euVatNumber, 2, strlen($euVatNumber) - 2);

        if(!function_exists('curl_init')) {
            return self::EU_VAT_VALIDATION_RESULT_ERROR;
        }

        $postArguments = [
            'action' => 'check',
            'check' => 'Verify',
            'memberStateCode' => $memberCountry,
            'number' => $vatNumber,
            'requestedMemberStateCode' => '',
            'requestedNumber' => '',
            'traderCity' => '',
            'traderName' => '',
            'traderPostalCode' => '',
            'traderStreet' => ''
        ];

        $c = curl_init();

        curl_setopt($c, CURLOPT_URL, 'http://ec.europa.eu/taxation_customs/vies/vatResponse.html?locale=en');
        curl_setopt($c, CURLOPT_POST, true);
        curl_setopt($c, CURLOPT_POSTFIELDS, http_build_query($postArguments));
        curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($c, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($c, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($c, CURLOPT_TIMEOUT, 30);
        curl_setopt($c, CURLOPT_CONNECTTIMEOUT, 15);
        curl_setopt($c, CURLOPT_FOLLOWLOCATION, true);

        $content = curl_exec($c);

        curl_close($c);
        if($content === false) {
            return self::EU_VAT_VALIDATION_RESULT_ERROR;
        }

        if(preg_match('/<b><span class="validStyle">/ism', $content)) {
            $cache[$euVatNumber] = (time() + 604800);
            $cache = array_filter($cache, function($expiresAt) {
                if($expiresAt >= time()) {
                    return true;
                }
            });

            set_transient('jigoshop_euvat', $cache, 604800);

            return self::EU_VAT_VALIDATION_RESULT_VALID;
        }
        elseif(preg_match('/<b><span class="invalidStyle">/ism', $content)) {
            return self::EU_VAT_VALIDATION_RESULT_INVALID;
        }

        return self::EU_VAT_VALIDATION_RESULT_ERROR;
    }
}
