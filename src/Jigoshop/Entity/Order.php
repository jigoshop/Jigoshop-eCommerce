<?php

namespace Jigoshop\Entity;

use Jigoshop\Entity\Customer\Guest;
use Jigoshop\Entity\Order\Discount;
use Jigoshop\Entity\Order\Item;
use Jigoshop\Entity\Order\Status;
use Jigoshop\Exception;
use Jigoshop\Helper\Currency;
use Jigoshop\Integration;
use Jigoshop\Payment;
use Jigoshop\Shipping;
use Monolog\Registry;

/**
 * Order class.
 *
 * @package Jigoshop\Entity
 * @author  Amadeusz Starzykiewicz
 */
class Order implements OrderInterface, \JsonSerializable
{
	const EU_VAT_VALIDATON_STATUS_INVALID = 'invalid';
	const EU_VAT_VALIDATON_STATUS_VALID = 'valid';

	/** @var int */
	private $id;
	/** @var string */
	private $key;
	/** @var string */
	private $number;
	/** @var \DateTime */
	private $createdAt;
	/** @var \DateTime */
	private $updatedAt;
	/** @var \DateTime */
	private $completedAt;
	/** @var Customer */
	private $customer;
	/** @var Item[] */
	private $items = [];
	/** @var Shipping\Method */
	private $shippingMethod;
	/** @var int */
	private $shippingMethodRate;
	/** @var Payment\Method */
	private $paymentMethod;
	/** @var float */
	private $productSubtotal;
	/** @var float */
	private $subtotal = 0.0;
	/** @var Discount[] */
	private $discounts = [];
	/** @var array */
	private $tax = [];
	/** @var array */
	private $taxDefinitions = [];
	/** @var boolean */
	private $removeTaxes = false;
	/** @var string */
	private $euVatValidationStatus = '';
	/** @var string */
	private $ipAddress = '';
	/** @var string */
	private $ipAddressCountry = '';
	/** @var array */
	private $shippingTax = [];
	/** @var float */
	private $totalTax;
	/** @var float */
	private $totalCombinedTax;
	/** @var float */
	private $shippingPrice = 0.0;
	/**	@var float */
	private $processingFee = null;
	/** @var string */
	private $currency = null;
	/** @var string */
	private $status = Status::PENDING;
	/** @var string */
	private $customerNote;
	/** @var array */
	private $updateMessages = [];
    /** @var bool  */
    private $taxIncluded = false;

	public function __construct(array $taxClasses)
	{
		$this->customer = new Guest();
		$this->createdAt = new \DateTime();
		$this->updatedAt = new \DateTime();
		$this->totalTax = null;
		$this->totalCombinedTax = null;

		foreach ($taxClasses as $class) {
			$this->tax[$class['class']] = 0.0;
			$this->shippingTax[$class['class']] = 0.0;
		}
	}

	/**
	 * @return int Entity ID.
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @param $id int Order ID.
	 */
	public function setId($id)
	{
		$this->id = $id;
	}

	/**
	 * @return string Title of the order.
	 */
	public function getTitle()
	{
		return sprintf(__('Order %s', 'jigoshop-ecommerce'), $this->getNumber());
	}

	/**
	 * @return int Order number.
	 */
	public function getNumber()
	{
		return $this->number;
	}

	/**
	 * @param string $number The order number.
	 */
	public function setNumber($number)
	{
		$this->number = $number;
	}

	/**
	 * @return string Order security key.
	 */
	public function getKey()
	{
		return $this->key;
	}

	/**
	 * @param string $key New security key for the order.
	 */
	public function setKey($key)
	{
		$this->key = $key;
	}

	/**
	 * @return \DateTime Time the order was created at.
	 */
	public function getCreatedAt()
	{
		return $this->createdAt;
	}

	/**
	 * @param \DateTime $createdAt Creation time.
	 */
	public function setCreatedAt($createdAt)
	{
		$this->createdAt = $createdAt;
	}

	/**
	 * @return \DateTime Time the order was updated at.
	 */
	public function getUpdatedAt()
	{
		return $this->updatedAt;
	}

	/**
	 * @param \DateTime $updatedAt Last update time.
	 */
	public function setUpdatedAt($updatedAt)
	{
		$this->updatedAt = $updatedAt;
	}

	/**
	 * @return \DateTime Time the order was completed.
	 */
	public function getCompletedAt() {
		return $this->completedAt;
	}

	/**
	 * Updates completion time to current or specified date.
	 * 
	 * @param \DateTime $time Order completed time.
	 */
	public function setCompletedAt($time = null)
	{
		if($time instanceof \DateTime) {
			$this->completedAt = $time;
		}
		else {
			$this->completedAt = new \DateTime();
		}
	}

	/**
	 * @return Customer The customer.
	 */
	public function getCustomer()
	{
		return $this->customer;
	}

	/**
	 * @param Customer $customer
	 */
	public function setCustomer($customer)
	{
		$this->customer = $customer;
	}


    /**
     * @param Discount $discount
     */
    public function addDiscount(Discount $discount)
    {
        $this->discounts[$discount->getCode()] = $discount;
	}

//    /**
//     * @param $key
//     *
//     * @return Discount|null
//     */
//	public function getDiscount($key)
//    {
//        if(isset($this->discounts[$key])) {
//            return $this->discounts[$key];
//        }
//
//        return null;
//    }

    /**
     * @return Discount[]
     */
    public function getDiscounts()
    {
        return $this->discounts;
    }

    /**
     * @param $key
     *
     * @return Discount|null
     */
    public function removeDiscount($key)
    {
        $discount = null;
        if(isset($this->discounts[$key])) {
            $discount = $this->discounts[$key];
            unset($this->discounts[$key]);
        }

        return $discount;
    }

    public function removeDiscounts()
    {
        $this->discounts = [];
    }


    /**
     * @return float
     */
    public function getDiscount()
    {
        if(empty($this->discounts)) {
            return 0.0;
        }
        //TODO: calculate it only once
        return array_sum(array_map(function ($discount) {
            /** @var Discount $discount */
            return $discount->getAmount();
        }, $this->discounts));
    }


	/**
	 * @return array Tax definitions.
	 */
	public function getTaxDefinitions()
	{
		if($this->removeTaxes) {
			return [];
		}
		
		return $this->taxDefinitions;
	}

	/**
	 * @param array $taxDefinitions New tax definitions.
	 */
	public function setTaxDefinitions($taxDefinitions)
	{
		//@TODO sprawdzić dlaczego key są czasami null'em
		$this->taxDefinitions = array_filter($taxDefinitions);
	}

	/**
	 * Returns item of selected key.
	 *
	 * @param $key string Item key to fetch.
	 *
	 * @return Item Order item.
	 * @throws Exception When item is not found.
	 */
	public function getItem($key)
	{
		if (!isset($this->items[$key])) {
			if (WP_DEBUG) {
				throw new Exception(sprintf(__('No item with ID %d in order %d', 'jigoshop-ecommerce'), $key, $this->id));
			}

			Registry::getInstance(JIGOSHOP_LOGGER)->addWarning(sprintf('No item with ID %d in order %d', $key, $this->id));

			return null;
		}

		return $this->items[$key];
	}

	/**
	 * Returns whether order contains selected item by it's key.
	 *
	 * @param $key string Item key to find.
	 *
	 * @return bool Whether order has the item.
	 */
	public function hasItem($key)
	{
		return isset($this->items[$key]);
	}

	/**
	 * Removes all items, shipping method and associated taxes from the order.
	 */
	public function removeItems()
	{
		$this->removeShippingMethod();
		$this->items = [];
		$this->productSubtotal = 0.0;
		$this->subtotal = 0.0;
		$this->tax = array_map(function (){
			return 0.0;
		}, $this->tax);
		$this->totalTax = null;
		$this->totalCombinedTax = null;
		$this->processingFee = null;
	}

	/**
	 * Removes shipping method and associated taxes from the order.
	 */
	public function removeShippingMethod()
	{
		$this->subtotal -= $this->shippingPrice;
		$this->shippingMethod = null;
		$this->shippingMethodRate = null;
		$this->shippingPrice = 0.0;
		$this->shippingTax = array_map(function (){
			return 0.0;
		}, $this->shippingTax);
		$this->totalCombinedTax = null;
		$this->processingFee = null;
	}

	/**
	 * @return Item[] List of items bought.
	 */
	public function getItems()
	{
		return $this->items;
	}

	/**
	 * @return Payment\Method Payment gateway object.
	 */
	public function getPaymentMethod()
	{
		return $this->paymentMethod;
	}

	/**
	 * @param Payment\Method $payment Method used to pay.
	 */
	public function setPaymentMethod($payment)
	{
		$this->paymentMethod = $payment;
	}

	/**
	 * @return float
	 */
	public function getShippingPrice()
	{
		return $this->shippingPrice;
	}

	/**
	 * @return Shipping\Method Shipping method.
	 */
	public function getShippingMethod()
	{
		return $this->shippingMethod;
	}

	/**
	 * @param Shipping\Method $method Method used for shipping the order.
	 */
	public function setShippingMethod(Shipping\Method $method)
	{
		$this->removeShippingMethod();

		if($this->isShippingRequired()) {
            $this->shippingMethod = $method;
            $this->shippingPrice = $method->calculate($this);
            $this->shippingPrice = apply_filters('jigoshop\shipping\get_price', $this->shippingPrice);
            $this->subtotal += $this->shippingPrice;
            $this->shippingTax = apply_filters('jigoshop\order\shipping_tax', $this->shippingTax, $method, $this);
            $this->totalCombinedTax = null;
        }
	}

	/**
	 * @param int $shippingMethodRate
	 */
	public function setShippingMethodRate($shippingMethodRate)
	{
		$this->shippingMethodRate = $shippingMethodRate;
	}

	/**
	 * Checks whether given shipping method is set for current cart.
	 *
	 * @param $method Shipping\Method Shipping method to check.
	 * @param $rate   Shipping\Rate Shipping rate to check.
	 *
	 * @return bool Is the method selected?
	 */
	public function hasShippingMethod($method, $rate = null)
	{
		if ($this->shippingMethod != null) {
			return $this->shippingMethod->is($method, $rate);
		}

		return false;
	}

	/**
	 * Checks whether at least one item requires shipping.
	 *
	 * @return bool Is shipping required for the cart?
	 */
	public function isShippingRequired()
	{
		$required = false;
		foreach ($this->items as $item) {
			/** @var $item Item */
			$product = $item->getProduct();
			if ($product instanceof Product\Shippable) {
				$required |= $product->isShippable();
			}
		}

		return $required;
	}

	/**
	 * @return string Current order status.
	 */
	public function getStatus()
	{
		return in_array($this->status, array_keys(Status::getStatuses())) ? $this->status : Status::PENDING;
	}

	/**
	 * @param string $status  Status to set.
	 * @param string $message Message to add with status change.
	 */
	public function setStatus($status, $message = '')
	{
		$currentStatus = $this->status;
		$this->status = $status;

		if ($currentStatus != $status) {
			$this->updateMessages[] = [
				'message' => (!$message ? '' : $message . '<br />'),
				'old_status' => $currentStatus,
				'new_status' => $status,
            ];
		}
	}

	/**
	 * @return string Customer's note on the order.
	 */
	public function getCustomerNote()
	{
		return $this->customerNote;
	}

	/**
	 * @param string $customerNote Customer's note on the order.
	 */
	public function setCustomerNote($customerNote)
	{
		$this->customerNote = $customerNote;
	}

	/**
	 * @return float
	 */
	public function getProductSubtotal()
	{
		return $this->productSubtotal;
	}

	/**
	 * @param float $productSubtotal
	 */
	public function setProductSubtotal($productSubtotal)
	{
		$this->productSubtotal = $productSubtotal;
	}

	/**
	 * @return float Subtotal value of the cart.
	 */
	public function getSubtotal()
	{
		return $this->subtotal;
	}

	/**
	 * @param float $subtotal New subtotal value.
	 */
	public function setSubtotal($subtotal)
	{
		$this->subtotal = $subtotal;
	}

	/**
	 * @return float Total value of the cart.
	 */
	public function getTotal()
	{
        //TODO: calculate it only once
		return (($this->subtotal + $this->getTotalCombinedTax()) - $this->getDiscount()) + $this->getProcessingFee();
	}

	/**
	 * @return array List of applied tax classes with it's values.
	 */
	public function getTax()
	{
		return $this->tax;
	}

	/**
	 * @param array $tax Tax data array.
	 */
	public function setTax($tax)
	{
		$this->totalTax = null;
		$this->totalCombinedTax = null;
		$this->tax = $tax;
	}

	/**
	 * Updates stored tax array with provided values.
	 *
	 * @param array $tax Tax divided by classes.
	 */
	public function updateTaxes(array $tax)
	{
		$this->totalTax = null;
		$this->totalCombinedTax = null;
		foreach ($tax as $class => $value) {
			$this->tax[$class] += $value;
		}
	}

	/**
	 * Returns whether Order has it's taxes removed.
	 * 
	 * @return boolean Taxes removed from Order.
	 */
	public function getTaxRemovalState() {
		return $this->removeTaxes;
	}

	/**
	 * Sets "removeTaxes" flag value.
	 * 
	 * @param boolean $state RemoveTaxes flag state.
	 */
	public function setTaxRemovalState($state) {
		$this->removeTaxes = $state;
	}

	/**
	 * Returns EU VAT validation status.
	 * 
	 * @return string EU VAT validation status.
	 */
	public function getEUVatValidationStatus() {
		return $this->euVatValidationStatus;
	}

	/**
	 * Sets EU VAT validation status.
	 * 
	 * @param string $euVatValidationStatus EU VAT validation status.
	 */
	public function setEUVatValidationStatus($euVatValidationStatus) {
		$this->euVatValidationStatus = $euVatValidationStatus;
	}

	/**
	 * Returns IP address used when order was placed.
	 * 
	 * @return string IP address.
	 */
	public function getIPAddress() {
		return $this->ipAddress;
	}

	/**
	 * Sets IP address used when order was placed.
	 * 
	 * @param string $ipAddress IP address to set.
	 */
	public function setIPAddress($ipAddress) {
		$this->ipAddress = $ipAddress;
	}

	/**
	 * Returns country code of IP address when order was placed.
	 *
	 * @return string Country code.
	 */
	public function getIPAddressCountry() {
		return $this->ipAddressCountry;
	}

	/**
	 * Sets country code of IP address when order was placed.
	 * 
	 * @param string $ipAddressCountry Country code.
	 */
	public function setIPAddressCountry($ipAddressCountry) {
		$this->ipAddressCountry = $ipAddressCountry;
	}

	/**
	 * @return array List of applied tax classes for shipping with it's values.
	 */
	public function getShippingTax()
	{
		return $this->shippingTax;
	}

	/**
	 * @param array $shippingTax Tax data array for shipping.
	 */
	public function setShippingTax($shippingTax)
	{
		$this->totalCombinedTax = null;
		$this->shippingTax = $shippingTax;
	}

	/**
	 * @return float Total tax of the order.
	 */
	public function getTotalTax()
	{
		if ($this->totalTax === null) {
			$this->totalTax = array_sum($this->tax);
		}

		return $this->totalTax;
	}

	/**
	 * @return float Total, combined tax of the order (includes shipping tax).
	 */
	public function getTotalCombinedTax()
	{
		if ($this->totalCombinedTax === null) {
			$this->totalCombinedTax = array_sum($this->getCombinedTax());
		}

		return $this->totalCombinedTax;
	}

	/**
	 * @return array All tax data combined.
	 */
	public function getCombinedTax()
	{
		$tax = $this->tax;
		foreach ($this->shippingTax as $class => $value) {
			if (!isset($tax[$class])) {
				$tax[$class] = 0.0;
			}

			$tax[$class] += $value;
		}

		if($this->removeTaxes) {
			foreach($tax as $class => $value) {
				$tax[$class] = 0.0;
			}		
		}

		return $tax;
	}

	/**
	 * Updates quantity of selected item by it's key.
	 *
	 * @param $key      string Item key in the order.
	 * @param $quantity int Quantity to set.
	 *
	 * @throws Exception When product does not exists or quantity is not numeric.
	 */
	public function updateQuantity($key, $quantity)
	{
		if (!isset($this->items[$key])) {
			throw new Exception(__('Item does not exists', 'jigoshop-ecommerce'));
		}

		if (!is_numeric($quantity)) {
			throw new Exception(__('Quantity has to be numeric value', 'jigoshop-ecommerce'));
		}

		$item = $this->removeItem($key);

		if ($item === null) {
			throw new Exception(__('Item not found.', 'jigoshop-ecommerce'));
		}

		if ($quantity <= 0) {
			return;
		}

		$item->setQuantity($quantity);
		$this->addItem($item);
	}

	/**
	 * @param $key string Item key to remove.
	 *
	 * @return Item Removed item.
	 */
	public function removeItem($key)
	{
		if (isset($this->items[$key])) {
			/** @var Item $item */
			$item = $this->items[$key];
			do_action('jigoshop\order\remove_item', $item, $this);
			$this->subtotal -= $item->getCost();
			$this->productSubtotal -= $item->getCost();
			$this->totalTax = null;
			$this->totalCombinedTax = null;
			$this->processingFee = null;
			unset($this->items[$key]);

			return $item;
		}

		return null;
	}

	/**
	 * @param Item $item Item to add.
	 */
	public function addItem(Item $item)
	{
		do_action('jigoshop\order\add_item', $item, $this);
		$this->items[$item->getKey()] = $item;
		$this->productSubtotal += $item->getCost();
		$this->subtotal += $item->getCost();
		$this->totalTax = null;
		$this->totalCombinedTax = null;
		$this->processingFee = null;
	}

	/**
	 * Returns matching processing fee rule.
	 * 
	 * @return mixed Processing fee rule or null if none is matching current Order.
	 */
	private function getProcessingFeeRule() {
		$processingFeeRules = Integration::getOptions()->get('payment.processingFeeRules', []);
		foreach($processingFeeRules as $rule) {
			if(is_array($rule['methods']) && in_array($this->paymentMethod->getId(), $rule['methods'])) {
				if($rule['minValue'] > 0 && $orderValue < $rule['minValue']) {
					continue;
				}

				if($rule['maxValue'] > 0 && $orderValue > $rule['maxValue']) {
					continue;
				}

				return $rule;
			}
		}
	}

	/**
	 * Returns processing fee value.
	 * 
	 * @return float Processing fee.
	 */
	public function getProcessingFee() {
		if($this->paymentMethod === null) {
			return 0;
		}

		$orderValue = ($this->subtotal + $this->getTotalCombinedTax()) - $this->getDiscount();

		if($this->processingFee === null) {
			$rule = $this->getProcessingFeeRule();
			if($rule === null) {
				return 0;
			}

			if(strstr($rule['value'], '%') !== false) {
				if($rule['alternateMode']) {
					$percent = str_replace('%', '', $rule['value']) / 100;
					$percent = 1.00 - $percent;
					$this->processingFee = ($orderValue / $percent) - $orderValue;
				}
				else {
					$percent = str_replace('%', '', $rule['value']) / 100;
					$this->processingFee = ($orderValue * $percent);
				}
			}
			else {
				$this->processingFee = $rule['value'];
			}
		}

		return $this->processingFee;
	}

	/**
	 * Returns processing fee as percent of Order value.
	 * 
	 * @return string Percent.
	 */
	public function getProcessingFeeAsPercent() {
		$fee = $this->getProcessingFee();
		if($fee == 0) {
			return '';
		}

		$orderValue = (($this->subtotal + $this->getTotalCombinedTax()) - $this->getDiscount());
		
		if($orderValue == 0) {
			return '0%';
		}

		$rule = $this->getProcessingFeeRule();
		if($rule['alternateMode']) {
			$orderValue += $fee;
		}

		$percent = number_format(($fee / $orderValue) * 100, 2);

		return $percent . '%';
	}

	/**
	 * Returns Order currency.
	 * 
	 * @return string Currency code (3 letters).
	 */
	public function getCurrency() {
		if($this->currency === null) {
			$this->currency = Currency::code();
		}

		return $this->currency;
	}

	/**
	 * Sets Order currency.
	 * 
	 * @param string $currency Currency code (3 letters).
	 */
	public function setCurrency($currency) {
		$this->currency = $currency;
	}

	/**
	 * @return array List of fields to update with according values.
	 */
	public function getStateToSave()
	{
		$shipping = false;
		if (is_object($this->shippingMethod)) {
			$shipping = $this->shippingMethod->getState();
		}

		$payment = false;
		if (is_object($this->paymentMethod)) {
			$payment = $this->paymentMethod->getId();
		}

		return [
			'id' => $this->id,
			'number' => $this->number,
			'updated_at' => $this->updatedAt->getTimestamp(),
			'completed_at' => $this->completedAt ? $this->completedAt->getTimestamp() : 0,
			'items' => $this->items,
			'customer' => serialize($this->customer),
			'customer_id' => $this->customer->getId(),
			'shipping' => [
				'method' => $shipping,
				'price' => $this->shippingPrice,
				'rate' => $this->shippingMethodRate,
            ],
			'payment' => $payment,
			'customer_note' => $this->customerNote,
			'subtotal' => $this->subtotal,
			'processingFee' => $this->getProcessingFee(),
			'currency' => $this->getCurrency(),
			'total' => $this->getTotal(),
			'discount' => $this->getDiscount(),
			'discounts' => $this->discounts,
			'shipping_tax' => $this->shippingTax,
			'status' => $this->getStatus(),
			'update_messages' => $this->updateMessages,
            'tax_included' => $this->taxIncluded,
            'removeTaxes' => $this->removeTaxes,
            'euVatValidationStatus' => $this->euVatValidationStatus,
            'ipAddress' => $this->ipAddress,
            'ipAddressCountry' => $this->ipAddressCountry
        ];
	}

    /**
     * @param bool $taxIncluded
     */
    public function setTaxIncluded($taxIncluded)
    {
        $this->taxIncluded = $taxIncluded;
    }

    /**
     * @return bool
     */
    public function isTaxIncluded()
    {
        return $this->taxIncluded;
    }

	/**
	 * @param array $state State to restore entity to.
	 */
	public function restoreState(array $state)
	{
		if (isset($state['key'])) {
			$this->key = $state['key'];
		}
		if (isset($state['number'])) {
			$this->number = $state['number'];
		}
		if (isset($state['created_at'])) {
			$this->createdAt->setTimestamp($state['created_at']);
		}
		if (isset($state['updated_at'])) {
			$this->updatedAt->setTimestamp($state['updated_at']);
		}
		if (isset($state['completed_at'])) {
			$this->completedAt = new \DateTime();
			$this->completedAt->setTimestamp($state['completed_at']);
		}
		if (isset($state['status'])) {
			$this->status = $state['status'];
		}
		if (isset($state['items'])) {
			foreach ($state['items'] as $item) {
				$this->addItem($item);
			}
		}
		if (isset($state['customer']) && $state['customer'] !== false) {
			$this->customer = $state['customer'];
		}
		if (isset($state['shipping']) && is_array($state['shipping'])) {
			$this->shippingMethod = $state['shipping']['method'];
			$this->shippingMethodRate = $state['shipping']['rate'];

			if ($state['shipping']['price'] > -1) {
				$this->shippingPrice = $state['shipping']['price'];
			} else {
				$this->shippingPrice = $this->shippingMethod->calculate($this);
			}

			$this->subtotal += $this->shippingPrice;
		}
		if (isset($state['payment']) && !empty($state['payment'])) {
			$this->paymentMethod = $state['payment'];
		}
		if (isset($state['customer_note'])) {
			$this->customerNote = $state['customer_note'];
		}
		if (isset($state['shipping_tax'])) {
			$tax = maybe_unserialize($state['shipping_tax']);
			foreach ($tax as $class => $value) {
				if (!isset($this->shippingTax[$class])) {
					$this->shippingTax[$class] = 0.0;
				}

				$this->shippingTax[$class] += $value;
			}
		}
		if (isset($state['product_subtotal'])) {
			$this->productSubtotal = (float)$state['product_subtotal'];
		}
		if (isset($state['discounts'])) {
			foreach ($state['discounts'] as $discount) {
			    $this->addDiscount($discount);
            }
		}
		if (isset($state['tax_definitions'])) {
			$this->taxDefinitions = $state['tax_definitions'];
		}
        if (isset($state['price_includes_tax'])) {
            $this->taxIncluded = (bool)$state['price_includes_tax'];
        }
        if(isset($state['processingFee'])) {
        	$this->processingFee = ($state['processingFee'] === 0?null:$state['processingFee']);
        }
        if(isset($state['currency'])) {
        	$this->currency = $state['currency'];
        }
        if(isset($state['removeTaxes'])) {
        	$this->removeTaxes = $state['removeTaxes'];
        }
        if(isset($state['euVatValidationStatus'])) {
        	$this->euVatValidationStatus = $state['euVatValidationStatus'];
        }
        if(isset($state['ipAddress'])) {
        	$this->ipAddress = $state['ipAddress'];
        }
        if(isset($state['ipAddressCountry'])) {
        	$this->ipAddressCountry = $state['ipAddressCountry'];
        }
	}

    /**
     * Used by json_encode method to proprly
     *
     * @return array
     */
    public function jsonSerialize()
    {
        $shipping = false;
        if (is_object($this->shippingMethod)) {
            $shipping = $this->shippingMethod->getState();
        }
        $payment = false;
        if (is_object($this->paymentMethod)) {
            $payment = $this->paymentMethod->getId();
        }
        $completedAt = false;
        if (is_object($this->completedAt) && $this->completedAt->getTimestamp()) {
            $completedAt = [
                'timestamp' => $this->completedAt->getTimestamp(),
                'format' => $this->completedAt->format('Y-m-d H:i:s')
            ];
        }

       return [
           'id' => $this->id,
           'number' => $this->number,
           'created_at' => [
               'timestamp' => $this->createdAt->getTimestamp(),
               'format' => $this->createdAt->format('Y-m-d H:i:s')
           ],
           'updated_at' => [
               'timestamp' => $this->updatedAt->getTimestamp(),
               'format' => $this->updatedAt->format('Y-m-d H:i:s')
           ],
           'completed_at' => $completedAt,
           'items' => array_values($this->items),
           'price_includes_tax' => $this->taxIncluded,
           'customer' => $this->customer,
           'shipping' => [
               'method' => $shipping,
               'price' => $this->shippingPrice,
               'rate' => $this->shippingMethodRate,
           ],
           'payment' => $payment,
           'customer_note' => $this->customerNote,
           'processingFee' => $this->getProcessingFee(),
           'currency' => $this->getCurrency(),
           'total' => $this->getTotal(),
           'tax' => $this->tax,
           'shipping_tax' => $this->shippingTax,
           'products_subtotal' => $this->productSubtotal,
           'subtotal' => $this->subtotal,
           'discounts' => $this->discounts,
           'discount' => $this->getDiscount(),
           'status' => $this->getStatus(),
           'update_messages' => $this->updateMessages,
       ];
    }
}
