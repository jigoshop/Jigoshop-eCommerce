<?php

namespace Jigoshop\Entity;

use Jigoshop\Entity\Order\Discount;
use Jigoshop\Entity\Order\Item;

/**
 * Shop coupon entity.
 *
 * @package Jigoshop\Entity
 */
class Coupon implements EntityInterface, \JsonSerializable
{
	const FIXED_CART = 'fixed_cart';
	const PERCENT_CART = 'percent_cart';
	const FIXED_PRODUCT = 'fixed_product';
	const PERCENT_PRODUCT = 'percent_product';

	/** @var int */
	private $id;
	/** @var string */
	private $title;
	/** @var int */
	private $type;
	/** @var string */
	private $code;
	/** @var float */
	private $amount;
	/** @var \DateTime */
	private $from;
	/** @var \DateTime */
	private $to;
	/** @var int */
	private $usage = 0;
	/** @var int */
	private $usageLimit;
	/** @var bool */
	private $individualUse = false;
	/** @var bool */
	private $freeShipping = false;
	/** @var float */
	private $orderTotalMinimum;
	/** @var float */
	private $orderTotalMaximum;
	/** @var array */
	private $products = [];
	/** @var array */
	private $excludedProducts = [];
	/** @var array */
	private $categories = [];
	/** @var array */
	private $excludedCategories = [];
	/** @var array */
	private $paymentMethods = [];

	/**
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @param int $id
	 */
	public function setId($id)
	{
		$this->id = $id;
	}

	/**
	 * @return string
	 */
	public function getTitle()
	{
		return $this->title;
	}

	/**
	 * @param string $title
	 */
	public function setTitle($title)
	{
		$this->title = $title;
	}

	/**
	 * @return array
	 */
	public function getPaymentMethods()
	{
		return $this->paymentMethods;
	}

	/**
	 * @param array $paymentMethods
	 */
	public function setPaymentMethods($paymentMethods)
	{
		$this->paymentMethods = $paymentMethods;
	}

	/**
	 * @return array
	 */
	public function getCategories()
	{
		return $this->categories;
	}

	/**
	 * @param array $categories
	 */
	public function setCategories($categories)
	{
		$this->categories = $categories;
	}

	/**
	 * @param int $category Category ID.
	 */
	public function addCategory($category)
	{
		$this->categories[] = $category;
	}

	/**
	 * @param int $category Category ID.
	 */
	public function removeCategory($category)
	{
		$key = array_search($category, $this->categories);
		if ($key !== false) {
			unset($this->categories[$key]);
		}
	}

	/**
	 * @return array
	 */
	public function getExcludedCategories()
	{
		return $this->excludedCategories;
	}

	/**
	 * @param array $excludedCategories
	 */
	public function setExcludedCategories($excludedCategories)
	{
		$this->excludedCategories = $excludedCategories;
	}

	/**
	 * @param int $category Category ID.
	 */
	public function addExcludedCategory($category)
	{
		$this->excludedCategories[] = $category;
	}

	/**
	 * @param int $category Category ID.
	 */
	public function removeExcludedCategory($category)
	{
		$key = array_search($category, $this->excludedCategories);
		if ($key !== false) {
			unset($this->excludedCategories[$key]);
		}
	}

	/**
	 * @return array
	 */
	public function getProducts()
	{
		return $this->products;
	}

	/**
	 * @param array $products
	 */
	public function setProducts($products)
	{
		$this->products = $products;
	}

	/**
	 * @param int $product Product ID.
	 */
	public function addProduct($product)
	{
		$this->products[] = $product;
	}

	/**
	 * @param int $product Product ID.
	 */
	public function removeProduct($product)
	{
		$key = array_search($product, $this->products);
		if ($key !== false) {
			unset($this->products[$key]);
		}
	}

	/**
	 * @return array
	 */
	public function getExcludedProducts()
	{
		return $this->excludedProducts;
	}

	/**
	 * @param array $excludedProducts
	 */
	public function setExcludedProducts($excludedProducts)
	{
		$this->excludedProducts = $excludedProducts;
	}

	/**
	 * @param int $product Product ID.
	 */
	public function addExcludedProduct($product)
	{
		$this->excludedProducts[] = $product;
	}

	/**
	 * @param int $product Product ID.
	 */
	public function removeExcludedProduct($product)
	{
		$key = array_search($product, $this->excludedProducts);
		if ($key !== false) {
			unset($this->excludedProducts[$key]);
		}
	}

	/**
	 * @return boolean
	 */
	public function isFreeShipping()
	{
		return $this->freeShipping;
	}

	/**
	 * @param boolean $freeShipping
	 */
	public function setFreeShipping($freeShipping)
	{
		$this->freeShipping = $freeShipping;
	}

	/**
	 * @return \DateTime
	 */
	public function getFrom()
	{
		return $this->from;
	}

	/**
	 * @param \DateTime $from
	 */
	public function setFrom($from)
	{
		$this->from = $from;
	}

	/**
	 * @return boolean
	 */
	public function isIndividualUse()
	{
		return $this->individualUse;
	}

	/**
	 * @param boolean $individualUse
	 */
	public function setIndividualUse($individualUse)
	{
		$this->individualUse = $individualUse;
	}

	/**
	 * @return float
	 */
	public function getOrderTotalMaximum()
	{
		return $this->orderTotalMaximum;
	}

	/**
	 * @param float $orderTotalMaximum
	 */
	public function setOrderTotalMaximum($orderTotalMaximum)
	{
		$this->orderTotalMaximum = $orderTotalMaximum;
	}

	/**
	 * @return float
	 */
	public function getOrderTotalMinimum()
	{
		return $this->orderTotalMinimum;
	}

	/**
	 * @param float $orderTotalMinimum
	 */
	public function setOrderTotalMinimum($orderTotalMinimum)
	{
		$this->orderTotalMinimum = $orderTotalMinimum;
	}

	/**
	 * @return \DateTime
	 */
	public function getTo()
	{
		return $this->to;
	}

	/**
	 * @param \DateTime $to
	 */
	public function setTo($to)
	{
		$this->to = $to;
	}

	/**
	 * @return int
	 */
	public function getType()
	{
		return $this->type;
	}

	/**
	 * @param int $type
	 */
	public function setType($type)
	{
		$this->type = $type;
	}

	/**
	 * @return int
	 */
	public function getUsage()
	{
		return $this->usage;
	}

	/**
	 * @param int $usage
	 */
	public function setUsage($usage)
	{
		$this->usage = $usage;
	}

	/**
	 * @return int
	 */
	public function getUsageLimit()
	{
		return $this->usageLimit;
	}

	/**
	 * @param int $usageLimit
	 */
	public function setUsageLimit($usageLimit)
	{
		$this->usageLimit = $usageLimit;
	}

	/**
	 * @return string
	 */
	public function getAmount()
	{
		return $this->amount;
	}

	/**
	 * @param string $amount
	 */
	public function setAmount($amount)
	{
		$this->amount = $amount;
	}

	/**
	 * @return string
	 */
	public function getCode()
	{
		return $this->code;
	}

	/**
	 * @param string $code
	 */
	public function setCode($code)
	{
		$this->code = $code;
	}

	public function getStateToSave()
	{
		return [
			'type' => $this->type,
			'amount' => $this->amount,
			'from' => $this->from ? $this->from->getTimestamp() : 0,
			'to' => $this->to ? $this->to->getTimestamp() : 0,
			'usage_limit' => $this->usageLimit,
			'usage' => $this->usage,
			'individual_use' => $this->individualUse,
			'free_shipping' => $this->freeShipping,
			'order_total_minimum' => $this->orderTotalMinimum,
			'order_total_maximum' => $this->orderTotalMaximum,
			'products' => $this->products,
			'excluded_products' => $this->excludedProducts,
			'categories' => $this->categories,
			'excluded_categories' => $this->excludedCategories,
			'payment_methods' => $this->paymentMethods,
        ];
	}

	public function restoreState(array $state)
	{
		if (isset($state['type'])) {
			$this->type = $state['type'];
		}
		if (isset($state['amount'])) {
			$this->amount = $state['amount'];
		}
		if (isset($state['from']) && $state['from']) {
			$this->from = new \DateTime();
			$this->from->setTimestamp($state['from']);
		}
		if (isset($state['to']) && $state['to']){
			$this->to = new \DateTime();
			$this->to->setTimestamp($state['to']);
		}
		if (isset($state['usage_limit'])) {
			$this->usageLimit = $state['usage_limit'];
		}
        if (isset($state['usage'])) {
            $this->usage = $state['usage'];
        }
		if (isset($state['individual_use'])) {
			$this->individualUse = $state['individual_use'];
		}
		if (isset($state['free_shipping'])) {
			$this->freeShipping = $state['free_shipping'];
		}
		if (isset($state['order_total_minimum'])) {
			$this->orderTotalMinimum = $state['order_total_minimum'];
		}
		if (isset($state['order_total_maximum'])) {
			$this->orderTotalMaximum = $state['order_total_maximum'];
		}
		if (isset($state['products'])) {
			$this->products = $state['products'];
		}
		if (isset($state['excluded_products'])) {
			$this->excludedProducts = $state['excluded_products'];
		}
		if (isset($state['categories'])) {
			$this->categories = $state['categories'];
		}
		if (isset($state['excluded_categories'])) {
			$this->excludedCategories = $state['excluded_categories'];
		}
		if (isset($state['payment_methods'])) {
			$this->paymentMethods = $state['payment_methods'];
		}
	}

	/**
	 * @param $order OrderInterface
	 *
	 * @return Discount
	 */
	public function getDiscount($order)
	{
	    $amount = 0;
		switch ($this->type) {
			case self::FIXED_CART:
				$amount = $this->amount;
				break;
			case self::PERCENT_CART:
                $amount = $this->amount * $order->getSubtotal() / 100;
                break;
			case self::FIXED_PRODUCT:
                $amount = 0.0;
				foreach ($order->getItems() as $item) {
					/** @var $item Item */
					if ($this->productMatchesCoupon($item->getProduct())) {
                        $amount += $item->getQuantity() * $this->amount;
					}
				};
                break;
			case self::PERCENT_PRODUCT:
                $amount = 0.0;
				foreach ($order->getItems() as $item) {
					/** @var $item Item */
					if ($this->productMatchesCoupon($item->getProduct())) {
                        $amount += $this->amount * $item->getCost() / 100;
					}
				}
                break;
		}

		$discount = new Discount();
		$discount->setCode($this->code);
		$discount->setType(Discount\Type::COUPON);
		$discount->setAmount($amount);
		$discount->addMeta(new Discount\Meta('coupon_data', json_encode($this)));
		$discount->addMeta(new Discount\Meta('free_shipping', $this->freeShipping ? 1 : 0));

		return apply_filters('jigoshop\entity\coupon\get_discount', $discount, $this);
	}

	/**
	 * @param $product Product Product to check.
	 *
	 * @return bool Is product good for the coupon.
	 */
	public function productMatchesCoupon($product)
	{
		if(!empty($this->products) && !in_array($product->getId(), $this->products)) {
			return false;
		}

		if(!empty($this->excludedProducts) && in_array($product->getId(), $this->excludedProducts)) {
			return false;
		}

		if(!empty($this->categories)) {
			$foundCategory = false;
			foreach($product->getCategories() as $category) {
				if(in_array($category['id'], $this->categories)) {
					$foundCategory = true;
					break;
				}
			}

			if(!$foundCategory) {
				return false;
			}
		}

		if(!empty($this->excludedCategories)) {
			foreach($product->getCategories() as $category) {
				if(in_array($category['id'], $this->excludedCategories)) {
					return false;
				}
			}
		}

		return true;
	}

    /**
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    function jsonSerialize()
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'amount' => $this->amount,
            'title' => $this->title,
            'from' => $this->from ? [
                'timestamp' => $this->from->getTimestamp(),
                'format' => $this->from->format('Y-m-d')
            ] : 0,
            'to' => $this->to ? [
                'timestamp' => $this->to->getTimestamp(),
                'format' => $this->to->format('Y-m-d')
            ] : 0,
            'usage_limit' => $this->usageLimit,
            'usage' => $this->usage,
            'individual_use' => $this->individualUse,
            'free_shipping' => $this->freeShipping,
            'order_total_minimum' => $this->orderTotalMinimum,
            'order_total_maximum' => $this->orderTotalMaximum,
            'products' => $this->products,
            'excluded_products' => $this->excludedProducts,
            'categories' => $this->categories,
            'excluded_categories' => $this->excludedCategories,
            'payment_methods' => $this->paymentMethods,
        ];
    }
}
