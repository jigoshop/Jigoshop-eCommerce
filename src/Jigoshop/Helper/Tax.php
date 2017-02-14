<?php

namespace Jigoshop\Helper;

use Jigoshop\Entity\OrderInterface;
use Jigoshop\Entity\Product as ProductEntity;
use Jigoshop\Service\CartServiceInterface;
use Jigoshop\Service\TaxServiceInterface;

class Tax
{
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
        if($product instanceof ProductEntity\Purchasable && self::$cartService) {
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
}
