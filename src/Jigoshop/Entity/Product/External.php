<?php

namespace Jigoshop\Entity\Product;

use Jigoshop\Entity\Product;
use Jigoshop\Exception;

class External extends Product implements Purchasable, Saleable
{
	const TYPE = 'external';

	/** @var float */
	private $price;
	/** @var float */
	private $regularPrice = 0.0;
	/** @var string */
	private $url;
	/** @var Attributes\Sales */
	private $sales;
	/** @var Product\Attributes\StockStatus */
	private $stock;

	public function __construct()
	{
        parent::__construct();
        $this->sales = new Attributes\Sales();
		$this->stock = new Product\Attributes\StockStatus();
	}

	/**
	 * @return string Product type.
	 */
	public function getType()
	{
		return self::TYPE;
	}

	/**
	 * Returns real product price.
	 * Applies `jigoshop\product\get_price` filter to allow plugins to modify the price.
	 *
	 * @return float Current product price.
	 */
	public function getPrice()
	{
		return apply_filters('jigoshop\product\get_price', $this->calculatePrice(), $this);
	}

	/**
	 * Sets product stock.
	 *
	 * @param Product\Attributes\StockStatus $stock New product stock status.
	 */
	public function setStock(Product\Attributes\StockStatus $stock)
	{
		throw new Exception(__('External product does not support stock management.', 'jigoshop-ecommerce'));
	}

	/**
	 * @return Product\Attributes\StockStatus Current stock status.
	 */
	public function getStock()
	{
		return $this->stock;
	}

	/**
	 * @return float Regular product price.
	 */
	public function getRegularPrice()
	{
		return $this->regularPrice;
	}

	/**
	 * @param float $regularPrice New regular product price.
	 */
	public function setRegularPrice($regularPrice)
	{
		$this->regularPrice = $regularPrice;
		$this->dirtyFields[] = 'regular_price';
	}

	/**
	 * @return string URL to external product.
	 */
	public function getUrl()
	{
		return $this->url;
	}

	/**
	 * @param string $url New URL to external product.
	 */
	public function setUrl($url)
	{
		$this->url = $url;
		$this->dirtyFields[] = 'url';
	}

	/**
	 * @return Attributes\Sales Current product sales data.
	 */
	public function getSales()
	{
		return $this->sales;
	}

	/**
	 * Sets product sales.
	 * Applies `jigoshop\product\set_sales` filter to allow plugins to modify sales data. When filter returns false sales are not modified at all.
	 *
	 * @param Attributes\Sales $sales Product sales data.
	 */
	public function setSales(Attributes\Sales $sales)
	{
		$sales = apply_filters('jigoshop\product\set_sales', $sales, $this);

		if ($sales !== false) {
			$this->sales = $sales;
			$this->dirtyFields[] = 'sales';
		}
	}

	private function calculatePrice()
	{
		if ($this->price !== null && $this->price >= 0) {
			return $this->price;
		}

		$price = $this->regularPrice;

		if ($this->sales !== null && \Jigoshop\Helper\Product::isOnSale($this)) {
			if (strpos($this->sales->getPrice(), '%') !== false) {
				$discount = trim($this->sales->getPrice(), '%');
				$sale = $this->regularPrice * (1 - $discount / 100);
			} else {
				$sale = $this->sales->getPrice();
			}

			if ($sale < $price) {
				$price = $sale;
			}
		}

		return $price;
	}

	/**
	 * @return array List of fields to update with according values.
	 */
	public function getStateToSave()
	{
		$toSave = parent::getStateToSave();

		foreach ($this->dirtyFields as $field) {
			switch ($field) {
				case 'regular_price':
					$toSave['regular_price'] = $this->regularPrice;
					break;
				case 'external_url':
					$toSave['external_url'] = $this->url;
					break;
			}
		}

		$toSave['sales_enabled'] = $this->sales->isEnabled();
		$toSave['sales_from'] = $this->sales->getFrom()->getTimestamp();
		$toSave['sales_to'] = $this->sales->getTo()->getTimestamp();
		$toSave['sales_price'] = $this->sales->getPrice();

		return $toSave;
	}

	/**
	 * @param array $state State to restore entity to.
	 */
	public function restoreState(array $state)
	{
		parent::restoreState($state);

		if (isset($state['regular_price'])) {
			$this->regularPrice = $state['regular_price'] !== '' ? (float)$state['regular_price'] : '';
		}
		if (isset($state['external_url'])) {
			$this->url = $state['external_url'];
		}
		if (isset($state['sales_enabled'])) {
			$this->sales->setEnabled((bool)$state['sales_enabled']);
		}
		if (isset($state['sales_from'])) {
			$this->sales->setFromTime($state['sales_from']);
		}
		if (isset($state['sales_to'])) {
			$this->sales->setToTime($state['sales_to']);
		}
		if (isset($state['sales_price'])) {
			$this->sales->setPrice($state['sales_price']);
		}

		$this->stock->setManage(false);
	}

	/**
	 * Marks values provided in the state as dirty.
	 *
	 * @param array $state Product state.
	 */
	public function markAsDirty(array $state)
	{
		$this->dirtyFields[] = 'sales';

		parent::markAsDirty($state);
	}

	/**
	 * @return array Minimal state to identify the product.
	 */
	public function getState()
	{
		return [
			'type' => $this->getType(),
			'id' => $this->getId(),
        ];
	}

	/**
	 * Checks whether the product requires shipping.
	 *
	 * @return bool Whether the product requires shipping.
	 */
	public function isShippable()
	{
		return false;
	}

    /**
     * Used by json_encode method to proprly
     *
     * @return array
     */
    public function jsonSerialize()
    {
        $state = parent::jsonSerialize();
        $state['regular_price'] = $this->regularPrice;
        $state['sale'] = $this->sales;
        $state['url'] = $this->url;

        return $state;
    }
}
