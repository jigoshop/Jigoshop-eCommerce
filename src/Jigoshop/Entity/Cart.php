<?php

namespace Jigoshop\Entity;

use Jigoshop\Entity\Customer;
use Jigoshop\Entity\Order\Item;
use Jigoshop\Entity\Product;
use Jigoshop\Entity\Product\Attributes\StockStatus;
use Jigoshop\Exception;
use Jigoshop\Frontend\NotEnoughStockException;
use Jigoshop\Helper\Product as ProductHelper;

class Cart extends Order
{
	/** @var Coupon[] */
	private $coupons = [];

	public function __construct(array $taxClasses)
	{
		parent::__construct($taxClasses);
	}

    /**
	 * Updates quantity of selected item by it's key.
	 *
	 * @param $key      string Item key in the cart.
	 * @param $quantity int Quantity to set.
	 *
	 * @throws Exception When product does not exists or quantity is not numeric.
	 */
	public function updateQuantity($key, $quantity)
	{
		if (!$this->hasItem($key)) {
			throw new Exception(__('Item does not exists', 'jigoshop-ecommerce'));
		}

		if (!is_numeric($quantity)) {
			throw new Exception(__('Quantity has to be numeric value', 'jigoshop-ecommerce'));
		}

		if ($quantity <= 0) {
			$this->removeItem($key);
			return;
		}

		$item = $this->getItem($key);
		$product = $item->getProduct();
        if($product instanceof Product\Variable) {
            $product = $product->getVariation($item->getMeta('variation_id')->getValue())->getProduct();
        }

		if ($product === null || $product->getId() === 0) {
			throw new Exception(__('Product not found', 'jigoshop-ecommerce'));
		}

		if ($product instanceof Product\Purchasable && !$this->checkStock($product, $quantity)) {
			throw new NotEnoughStockException($product->getStock()->getStock());
		}

		parent::updateQuantity($key, $quantity);
	}

	/**
	 * Adds item to the cart.
	 * If item is already present - increases it's quantity.
	 *
	 * @param Item $item Item to add to cart.
	 *
	 * @throws NotEnoughStockException When user requests more than we have.
	 * @throws Exception On any error.
	 */
	public function addItem(Item $item)
	{
		$product = $item->getProduct();

        if($product instanceof Product\Variable) {
		    $product = $product->getVariation($item->getMeta('variation_id')->getValue())->getProduct();
        }
		$quantity = $item->getQuantity();

		if ($product === null || $product->getId() === 0) {
			throw new Exception(__('Product not found', 'jigoshop-ecommerce'));
		}

		if ($quantity <= 0) {
			throw new Exception(__('Quantity has to be positive number', 'jigoshop-ecommerce'));
		}

		if ($this->hasItem($item->getKey())) {
			/** @var Item $itemInCart */
			$itemInCart = $this->getItem($item->getKey());
			if ($product instanceof Product\Purchasable && !$this->checkStock($product, $itemInCart->getQuantity() + $item->getQuantity())) {
				throw new NotEnoughStockException($product->getStock()->getStock());
			}

			$this->updateQuantity($itemInCart->getKey(), ($itemInCart->getQuantity() + $item->getQuantity()));

			return;
		}

		if ($product instanceof Product\Purchasable && !$this->checkStock($product, $quantity)) {
			throw new NotEnoughStockException($product->getStock()->getStock());
		}

		$isValid = apply_filters('jigoshop\cart\validate_new_item', true, $product->getId(), $item->getQuantity());
		if (!$isValid) {
			throw new Exception(__('Could not add to cart.', 'jigoshop-ecommerce'));
		}

		$item = apply_filters('jigoshop\cart\new_item', $item);
		parent::addItem($item);
	}

	/**
	 * @param $product  Product\Purchasable
	 * @param $quantity int
	 *
	 * @return bool
	 */
	private function checkStock($product, $quantity)
	{
		if (!$product->getStock()->getManage()) {
			return $product->getStock()->getStatus() == StockStatus::IN_STOCK;
		}

		if ($quantity > $product->getStock()->getStock()) {
			if (in_array($product->getStock()->getAllowBackorders(), [
				StockStatus::BACKORDERS_ALLOW,
				StockStatus::BACKORDERS_NOTIFY
            ])) {
				return true;
			}

			return false;
		}

		return true;
	}

	/**
	 * @return bool Is the cart empty?
	 */
	public function isEmpty()
	{
		$items = $this->getItems();

		return empty($items);
	}

	/**
	 * @return Coupon[] Coupons list.
	 */
	public function getCoupons()
	{
		return $this->coupons;
	}

	/**
	 * @return bool Whether cart has coupons applied.
	 */
	public function hasCoupons()
	{
		return !empty($this->coupons);
	}

	/**
	 * Removes and adds each coupon currently applied to the cart. This causes to recalculate discount values.
	 */
	public function recalculateCoupons()
	{
		foreach ($this->coupons as $coupon) {

			$this->removeCoupon($coupon->getId());
			try {
				$this->addCoupon($coupon);
			} catch (Exception $e) {
				// TODO: Some idea how to report this to the user?
			}
		}
	}

	/**
	 * @param $id int Coupon ID.
	 */
	public function removeCoupon($id)
	{
		if (!isset($this->coupons[$id])) {
			return;
		}
        $this->removeDiscount($this->coupons[$id]->getCode());
		unset($this->coupons[$id]);
	}

	/**
	 * @param Coupon $coupon
	 */
	public function addCoupon(Coupon $coupon)
	{
		if (isset($this->coupons[$coupon->getId()])) {
			return;
		}

		if ($coupon->getUsageLimit() > 0 && $coupon->getUsageLimit() <= $coupon->getUsage()) {
		    throw new Exception(sprintf(__('Cannot apply coupon "%s". The usage limit was reached.', 'jigoshop-ecommerce'), $coupon->getCode()));
        }

		if (is_numeric($coupon->getOrderTotalMinimum()) && $this->getTotal() < $coupon->getOrderTotalMinimum()) {
			throw new Exception(sprintf(__('Cannot apply coupon "%s". Order total less than %s.'), $coupon->getCode(), ProductHelper::formatPrice($coupon->getOrderTotalMinimum())));
		}
		if (is_numeric($coupon->getOrderTotalMaximum()) && $this->getTotal() > $coupon->getOrderTotalMaximum()) {
			throw new Exception(sprintf(__('Cannot apply coupon "%s". Order total more than %s.'), $coupon->getCode(), ProductHelper::formatPrice($coupon->getOrderTotalMaximum())));
		}

		if ($coupon->isIndividualUse()) {
			$this->removeAllCouponsExcept([]);
		}

		$discount = $coupon->getDiscount($this);
		$this->coupons[$coupon->getId()] = $coupon;

		$this->addDiscount($discount);
	}

	/**
	 * Removes all coupons except ones listed in the parameter.
	 *
	 * @param $codes array List of actual coupon codes.
	 */
	public function removeAllCouponsExcept($codes)
	{
		foreach ($this->coupons as $coupon) {
			if (!in_array($coupon->getCode(), $codes)) {
				$this->removeCoupon($coupon->getId());
			}
		}
	}

    /**
     * @return array
     */
	public function getStateToSave()
	{
		$state = parent::getStateToSave();
		$state['items'] = serialize($state['items']);
		$state['coupons'] = serialize($this->coupons);
		unset($state['update_messages'], $state['updated_at'], $state['completed_at'], $state['total'],
			$state['subtotal']);

		return $state;
	}

    /**
     * @param array $state
     */
    public function restoreState(array $state)
    {
        parent::restoreState($state);
        if(isset($state['coupons'])) {
            $this->coupons = maybe_unserialize($state['coupons']);
        }
	}
}
