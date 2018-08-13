<?php

namespace Jigoshop\Entity\Product\Variable;

use Jigoshop\Container;
use Jigoshop\Entity\JsonInterface;
use Jigoshop\Entity\Product;
use Jigoshop\Entity\Product\Variable;
use Jigoshop\Factory\Product\VariableService;
use Jigoshop\Service\ProductService;

/**
 * Entity for variation of the product.
 *
 * @package Jigoshop\Entity\Product\Variable
 */
class Variation implements JsonInterface
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
			$this->title = sprintf(_x('%s (%s)', 'product_variation', 'jigoshop-ecommerce'), $this->parent->getName(), join(', ', array_filter(array_map(function ($item){
				/** @var $item Attribute */
				$value = $item->getValue();
				if (is_numeric($value) && $value > 0) {
					return sprintf(_x('%s: %s', 'product_variation', 'jigoshop-ecommerce'), $item->getAttribute()->getLabel(), $item->getAttribute()->getOption($value)->getLabel());
				} else {
					return sprintf(_x('%s: any', 'product_variation', 'jigoshop-ecommerce'), $item->getAttribute()->getLabel());
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

    /**
     * @param Container $di
     * @param array $json
     */
    public function jsonDeserialize(Container $di, array $json)
    {
        if(isset($json['id'])) {
            $this->id = $json['id'];
        }

        if(isset($json['product'])) {
            /** @var \Jigoshop\Service\Product\VariableService $service */
            $service = $di->get('jigoshop.service.product.variable');
            if(isset($json['product']['id'])) {
                var_dump($this->getProduct());exit;
                $variation = $service->find($this->parent, (int)$json['product']['id']);
                $product = $variation->getProduct();
            } else {
                $product = $service->createVariableProduct($this, $this->parent, isset($json['product']['type']) ? $json['product']['type'] : Product\Simple::TYPE);
            }
            if(!$product instanceof Product\Variable) {
                $product->jsonDeserialize($di, $json['product']);
                $this->setProduct($product);
                $this->setId($product->getId());
            }
        }

        if(isset($json['attributes'])) {
            foreach($json['attributes'] as $jsonAttribute) {
                if(isset($jsonAttribute['attribute'], $jsonAttribute['attribute']['id']) && $this->hasAttribute($jsonAttribute)) {
                    $attribute = $this->getAttribute($jsonAttribute['attribute']['id']);
                } else {
                    $attribute = new Attribute();
                }

                $attribute->jsonDeserialize($di, $jsonAttribute);
                $this->addAttribute(clone $attribute);
            }
        }
    }

    /**
     * Called by var_dump();
     *
     * @return array
     */
    public function __debugInfo()
    {
        return $this->jsonSerialize();
    }
}
