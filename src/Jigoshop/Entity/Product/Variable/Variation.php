<?php

namespace Jigoshop\Entity\Product\Variable;

use Jigoshop\Entity\Product;
use Jigoshop\Entity\Product\Variable;

/**
 * Entity for variation of the product.
 *
 * @package Jigoshop\Entity\Product\Variable
 */
class Variation implements \JsonSerializable
{
	/** @var int */
	private $id;
	/** @var Variable */
	private $parent;
	/** @var Product|Product\Purchasable */
	private $product;
	/** @var Attribute[] */
	private $attributes = [];
	/** @var string Cache for title */
	private $title;

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
	 * @return string Variation title.
	 */
	public function getTitle()
	{
		// TODO: Title changing description in docs
		if ($this->title === null) {
			$this->title = sprintf(_x('%s (%s)', 'product_variation', 'jigoshop'), $this->parent->getName(), join(', ', array_filter(array_map(function ($item){
				/** @var $item Attribute */
				$value = $item->getValue();
				if (is_numeric($value) && $value > 0) {
					return sprintf(_x('%s: %s', 'product_variation', 'jigoshop'), $item->getAttribute()->getLabel(), $item->getAttribute()->getOption($value)->getLabel());
				} else {
					return sprintf(_x('%s: any', 'product_variation', 'jigoshop'), $item->getAttribute()->getLabel());
				}

				return '';
			}, $this->attributes))));
		}

		return $this->title;
	}

	/**
	 * @return Variable
	 */
	public function getParent()
	{
		return $this->parent;
	}

	/**
	 * @param Variable $parent
	 */
	public function setParent($parent)
	{
		$this->title = null;
		$this->parent = $parent;
	}

	/**
	 * @return Product|Product\Purchasable|Product\Saleable
	 */
	public function getProduct()
	{
		return $this->product;
	}

	/**
	 * @param Product|Product\Purchasable|Product\Saleable $product
	 */
	public function setProduct($product)
	{
		$this->product = $product;
	}

	/**
	 * @param Attribute $attribute
	 */
	public function addAttribute($attribute)
	{
		$this->title = null;
		$attribute->setVariation($this);
		$this->attributes[$attribute->getAttribute()->getId()] = $attribute;
	}

	/**
	 * @param $id int Attribute ID.
	 *
	 * @return bool Whether variation already has this attribute.
	 */
	public function hasAttribute($id)
	{
		return isset($this->attributes[$id]);
	}

	/**
	 * @param $id int Attribute ID.
	 *
	 * @return Attribute Variation attribute.
	 */
	public function getAttribute($id)
	{
		if (!isset($this->attributes[$id])) {
			return null;
		}

		return $this->attributes[$id];
	}

	/**
	 * @return Attribute[]
	 */
	public function getAttributes()
	{
		return $this->attributes;
	}

    /**
     * Used by json_encode method to proprly
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return [
            'id' => $this->id,
            'title' => $this->getTitle(),
            'product' => $this->product,
            'attributes' => array_values($this->attributes),
        ];
    }
}
