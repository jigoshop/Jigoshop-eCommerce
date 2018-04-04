<?php

namespace Jigoshop\Entity\Product;

use Jigoshop\Entity\Order\Item;
use Jigoshop\Entity\Product;
use Jigoshop\Entity\Product\Attribute;
use Jigoshop\Entity\Product\Attributes\StockStatus;
use Jigoshop\Integration;

class Variable extends Product implements Shippable, Saleable
{
	const TYPE = 'variable';

    /** @var Product\Variable\Variation[] */
	private $variations = [];
	/** @var Attributes\Sales */
	private $sales;
    /** @var int */
    private $defaultVariationId;

	public function __construct()
	{
        parent::__construct();
        $this->sales = new Attributes\Sales();
	}

	/**
	 * Checks whether the product requires shipping.
	 *
	 * @return bool Whether the product requires shipping.
	 */
	public function isShippable()
	{
		return array_reduce($this->variations, function ($value, $item){
			/** @var $item Item */
			$product = $item->getProduct();

			return $value & ($product instanceof Shippable && $product->isShippable());
		}, true);
	}

	/**
	 * @param Variable\Variation $variation Variation to add.
	 */
	public function addVariation(Product\Variable\Variation $variation)
	{
		$this->variations[$variation->getId()] = $variation;
	}

	/**
	 * Returns variation instance for selected ID.
	 * If ID is not found - returns null.
	 *
	 * @param $id int Variation ID.
	 *
	 * @return Product\Variable\Variation Variation found.
	 */
	public function removeVariation($id)
	{
		if (!isset($this->variations[$id])) {
			return null;
		}

		$variation = $this->variations[$id];
		unset($this->variations[$id]);

		return $variation;
	}

	/**
	 * @param int $id Variation ID.
	 *
	 * @return bool Variation exists?
	 */
	public function hasVariation($id)
	{
		return isset($this->variations[$id]);
	}

	/**
	 * @param $id int Variation ID.
	 *
	 * @return Product\Variable\Variation
	 */
	public function getVariation($id)
	{
		if (!isset($this->variations[$id])) {
			return null;
		}

		return $this->variations[$id];
	}

	/**
	 * @return Product\Variable\Variation[] List of all assigned variations.
	 */
	public function getVariations()
	{
		return $this->variations;
	}

	/**
	 * @return float Minimum price of all variations.
	 */
	public function getLowestPrice()
	{
		$prices = array_filter(array_map(function ($item){
			/** @var $item Product\Variable\Variation */
			return $item->getProduct()->getPrice();
		}, $this->variations));

		return !empty($prices) ? min($prices) : '';
	}

	/**
	 * @return float Maximum price of all variations.
	 */
	public function getHighestPrice()
	{
		$prices = array_filter(array_map(function ($item){
			/** @var $item Product\Variable\Variation */
			return $item->getProduct()->getPrice();
		}, $this->variations));

		return !empty($prices) ? max($prices) : '';
	}

	/**
	 * @return string Product type.
	 */
	public function getType()
	{
		return self::TYPE;
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

	/**
	 * @return array List of variable attributes.
	 */
	public function getVariableAttributes()
	{
		return array_filter($this->getAttributes(), function ($item){
			/** @var $item Product\Attribute\Variable */
			return $item instanceof Product\Attribute\Variable && $item->isVariable();
		});
	}

    /**
     * @return array
     */
    public function getAssignedVariableAttributes()
    {
    	$hideOutOfStockVariations = Integration::getOptions()->get('products.hide_out_of_stock_variations', false);

        $attributes = [];
        foreach($this->variations as $variation) {
        	if($hideOutOfStockVariations) {
        		if($variation->getProduct()->getStock()->getStatus() == StockStatus::OUT_STOCK) {
        			continue;
        		}
        	}

            foreach($variation->getAttributes() as $attribute) {
                if(!isset($attributes[$attribute->getAttribute()->getId()])) {
                    $attributes[$attribute->getAttribute()->getId()] = [
                        'label' => $attribute->getAttribute()->getLabel(),
                        'options' => []
                    ];
                }

                if($attribute->getValue()) {
                    $attributes[$attribute->getAttribute()->getId()]['options'][$attribute->getValue()] = $attribute->getAttribute()->getOption($attribute->getValue())->getLabel();
                } else {
                    foreach($attribute->getAttribute()->getOptions() as $option) {
                        if(in_array($option->getId(), $attribute->getAttribute()->getValue())){
                            $attributes[$attribute->getAttribute()->getId()]['options'][$option->getId()] = $option->getLabel();
                        }
                    }
                }
            }
        }

        return $attributes;
	}

    /**
     * @param int $defaultVariationId
     */
    public function setDefaultVariation($defaultVariationId)
    {
        $this->defaultVariationId = $defaultVariationId;
	}

    /**
     * @return int
     */
    public function getDefaultVariationId()
    {
        return $this->defaultVariationId;
	}

	/**
	 * @return array List of fields to update with according values.
	 */
	public function getStateToSave()
	{
		$toSave = parent::getStateToSave();

		$toSave['sales_enabled'] = $this->sales->isEnabled();
		$toSave['sales_from'] = $this->sales->getFrom()->getTimestamp();
		$toSave['sales_to'] = $this->sales->getTo()->getTimestamp();
		$toSave['sales_price'] = $this->sales->getPrice();
        $toSave['default_variation_id'] = $this->defaultVariationId;

		return $toSave;
	}

	/**
	 * @param array $state State to restore entity to.
	 */
	public function restoreState(array $state)
	{
		parent::restoreState($state);

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
		if (isset($state['default_variation_id'])) {
            $this->defaultVariationId = (int)$state['default_variation_id'];
        }
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
     * Used by json_encode method to proprly
     *
     * @return array
     */
    public function jsonSerialize()
    {
        $state = parent::jsonSerialize();
        $state['sale'] = $this->sales;
        $state['lowest_price'] = $this->getLowestPrice();
        $state['highest_price'] = $this->getHighestPrice();
        $state['variations'] = $this->variations;
        $state['default_variable_attributes_values'] = $this->defaultVariationId;

        return $state;
    }
}
