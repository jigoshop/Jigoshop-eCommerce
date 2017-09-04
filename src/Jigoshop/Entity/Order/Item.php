<?php

namespace Jigoshop\Entity\Order;

use Jigoshop\Entity\Product;
use Jigoshop\Entity\Product\Attributes;
use Jigoshop\Exception;
use Monolog\Registry;

/**
 * Order item.
 *
 * @package Jigoshop\Entity\Order
 * @author  Amadeusz Starzykiewicz
 */
class Item implements Product\Purchasable, Product\Taxable, \Serializable, \JsonSerializable
{
	/** @var int */
	private $id;
	/** @var string */
	private $key;
	/** @var string */
	private $name;
	/** @var int */
	private $quantity = 0;
	/** @var float */
	private $price = 0.0;
	/** @var float */
	private $tax = 0.0;
	/** @var array */
	private $taxClasses = [];
	/** @var int */
	private $productId;
	/** @var Product|Product\Purchasable|Product\Shippable */
	private $product;
	/** @var string */
	private $type;
	/** @var array */
	private $meta = [];

	/**
	 * @return int Item ID.
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @param int $id New ID for item.
	 */
	public function setId($id)
	{
		$this->id = $id;
	}

	/**
	 * Returns distinctive item key based on all product data and generated in product service.
	 *
	 * @return string Item key.
	 */
	public function getKey()
	{
		return $this->key;
	}

	/**
	 * @param string $key New item key.
	 */
	public function setKey($key)
	{
		$this->key = $key;
	}

	/**
	 * @return string Product name.
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @param string $name New name of the product.
	 */
	public function setName($name)
	{
		$this->name = $name;
	}

	/**
	 * @return int Item quantity.
	 */
	public function getQuantity()
	{
		return $this->quantity;
	}

	/**
	 * @param int $quantity New item quantity.
	 *
	 * @throws Exception When quantity is invalid.
	 */
	public function setQuantity($quantity)
	{
		if ($quantity < 0) {
			if (WP_DEBUG) {
				throw new Exception(__('Item quantity cannot be below 0', 'jigoshop-ecommerce'));
			}

			Registry::getInstance(JIGOSHOP_LOGGER)->addCritical('Item quantity cannot be below 0');
			$quantity = 0;
		}

		$this->quantity = $quantity;
	}

	/**
	 * @return float Regular product price.
	 */
	public function getRegularPrice()
	{
		return $this->price;
	}

	/**
	 * @return float Single item price.
	 */
	public function getPrice()
	{
		return $this->price;
	}

	/**
	 * @param float $price New price of single item.
	 */
	public function setPrice($price)
	{
		$this->price = $price;
	}

	/**
	 * @return float Total tax.
	 */
	public function getTax()
	{
		return $this->tax;
	}

	/**
	 * @param float $tax New tax value.
	 */
	public function setTax($tax)
	{
		$this->tax = $tax;
	}

	/**
	 * @return float Item cost excluding tax.
	 */
	public function getCost()
	{
		return $this->price * $this->quantity;
	}

	/**
	 * @return int ID of the product.
	 */
	public function getProductId()
	{
		return $this->productId;
	}

	/**
	 * @return Product|Product\Purchasable|null The product.
	 */
	public function getProduct()
	{
		return $this->product;
	}

	/**
	 * @param Product $product New product for the item.
	 */
	public function setProduct(Product $product)
	{
		$this->productId = $product->getId();
		$this->product = $product;
        // Due to backward compatibility with database version 1.
        if(empty($this->taxClasses)) {
            $this->taxClasses = $product->getTaxClasses();
        }
        // Due to backward compatibility with database version 1.
        if(empty($this->type)) {
            $this->type = $product->getType();
        }
	}

    /**
     * @param string $type Product type
     */
    public function setType($type)
    {
        $this->type = $type;
	}

	/**
	 * @return string Product type.
	 */
	public function getType()
	{
		return $this->type;
	}

	/**
	 * @return array List of applicable tax classes.
	 */
	public function getTaxClasses()
	{
		return $this->taxClasses;
	}

	/**
	 * Allows to override tax classes added by setProduct().
	 * @see Jigoshop\Core\Types\Product\Variable::addToCart() Used to set tax classes for single variable
	 *
	 * @param mixed $taxClasses.
	 */
	public function setTaxClasses($taxClasses)
	{
        if(is_string($taxClasses)) {
            $taxClasses = $taxClasses ? explode(',', $taxClasses) : [];
        }

		$this->taxClasses = $taxClasses;
	}

	/**
	 * Adds meta value to the item.
	 *
	 * @param Item\Meta $meta Meta value to add.
	 */
	public function addMeta(Item\Meta $meta)
	{
		$meta->setItem($this);
		$this->meta[$meta->getKey()] = $meta;
	}

	/**
	 * Removes meta value from the item and returns it.
	 *
	 * @param string $key Meta key.
	 *
	 * @return Item\Meta Meta object.
	 */
	public function removeMeta($key)
	{
		$meta = $this->getMeta($key);

		if ($meta === null) {
			return null;
		}

		unset($this->meta[$key]);

		return $meta;
	}

	/**
	 * Returns single meta object.
	 *
	 * @param string $key Meta key.
	 *
	 * @return Item\Meta Meta object.
	 */
	public function getMeta($key)
	{
		if (!isset($this->meta[$key])) {
			return null;
		}

		return $this->meta[$key];
	}

	/**
	 * @return array All meta values assigned to the item.
	 */
	public function getAllMeta()
	{
		return $this->meta;
	}

	/**
	 * (PHP 5 &gt;= 5.1.0)<br/>
	 * String representation of object
	 *
	 * @link http://php.net/manual/en/serializable.serialize.php
	 * @return string the string representation of the object or null
	 */
	public function serialize()
	{
		return serialize([
			'id' => $this->id,
			'key' => $this->key,
			'name' => $this->name,
			'type' => $this->type,
			'quantity' => $this->quantity,
			'price' => $this->price,
			'tax' => $this->tax,
			'tax_classes' => serialize($this->taxClasses),
			'product_id' => $this->product->getId(),
			'product' => $this->product->getState(),
			'meta' => serialize($this->meta),
        ]);
	}

	/**
	 * (PHP 5 &gt;= 5.1.0)<br/>
	 * Constructs the object
	 *
	 * @link http://php.net/manual/en/serializable.unserialize.php
	 *
	 * @param string $serialized <p>
	 *                           The string representation of the object.
	 *                           </p>
	 *
	 * @return void
	 */
	public function unserialize($serialized)
	{
		$data = unserialize($serialized);
		$this->id = $data['id'];
		$this->key = $data['key'];
		$this->name = $data['name'];
		$this->type = $data['type'];
		$this->quantity = $data['quantity'];
		$this->price = $data['price'];
		$this->tax = $data['tax'];
		$this->taxClasses = unserialize($data['tax_classes']);
		$this->meta = unserialize($data['meta']);
		$this->productId = $data['product_id'];
		$this->product = apply_filters('jigoshop\internal\order\item', null, $data['product']);

		foreach ($this->meta as $meta) {
			/** @var $meta Item\Meta */
			$meta->setItem($this);
		}
	}

	/**
	 * Returns stock data.
	 *
	 * @return Attributes\StockStatus Current stock status.
	 */
	public function getStock()
	{
		throw new Exception(__('Items do not have stock.', 'jigoshop-ecommerce'));
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
            'key' => $this->key,
            'name' => $this->name,
            'type' => $this->type,
            'quantity' => $this->quantity,
            'price' => $this->price,
            'tax' => $this->tax,
            'tax_classes' => $this->taxClasses,
            'product' => $this->product->getId(),
            'meta' => array_values($this->meta),
        ];
    }
}
