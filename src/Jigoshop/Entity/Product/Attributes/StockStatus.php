<?php

namespace Jigoshop\Entity\Product\Attributes;

/**
 * Product's stock status.
 *
 * @package Jigoshop\Entity\Product\Attributes
 * @author  Amadeusz Starzykiewicz
 */
class StockStatus implements \Serializable, \JsonSerializable
{
	const OUT_STOCK = 0;
	const IN_STOCK = 1;

	const BACKORDERS_ALLOW = 'yes';
	const BACKORDERS_FORBID = 'no';
	const BACKORDERS_NOTIFY = 'notify';

	/** @var boolean */
	private $manage = false;
	/** @var int */
	private $status = self::IN_STOCK;
	/** @var string */
	private $allowBackorders = 'no';
	/** @var int */
	private $stock = 0;
	/** @var int */
	private $soldQuantity = 0;

	/**
	 * @param string $allowBackorders Allow backorders?
	 */
	public function setAllowBackorders($allowBackorders)
	{
		$this->allowBackorders = $allowBackorders;
	}

	/**
	 * @param boolean $manage Manage product's stock?
	 */
	public function setManage($manage)
	{
		$this->manage = (boolean)$manage;
	}

	/**
	 * @param int $soldQuantity Number of products sold.
	 */
	public function setSoldQuantity($soldQuantity)
	{
		$this->soldQuantity = (int)$soldQuantity;
	}

	/**
	 * @param int $soldQuantity Number of products sold.
	 */
	public function addSoldQuantity($soldQuantity)
	{
		$this->soldQuantity += (int)$soldQuantity;
		$this->stock -= (int)$soldQuantity;
		if($this->stock <= 0) {
			$this->setStatus(self::OUT_STOCK);
		}
	}

    /**
     * @param $quantity Number of products to restore.
     */
    public function restoreStock($quantity)
    {
        $this->soldQuantity = $this->soldQuantity - (int)$quantity > 0 ? $this->soldQuantity - (int)$quantity : 0;
        $this->modifyStock((int)$quantity);
	}

	/**
	 * Sets product status.
	 * Please use provided constants:
	 *   * StockStatus::IN_STOCK - product is in stock
	 *   * StockStatus::OUT_STOCK - product is out of stock
	 *
	 * @param int $status Product status.
	 */
	public function setStatus($status)
	{
		$this->status = $status;
	}

	/**
	 * @param int $stock Number of products in stock.
	 */
	public function setStock($stock)
	{
		$this->stock = $stock;
		if($this->getManage() && $this->stock > 0){
			$this->setStatus(self::IN_STOCK);
		} else {
			$this->setStatus(self::OUT_STOCK);
		}
	}

	/**
	 * @param int $stock Value to add to stock.
	 */
	public function modifyStock($stock)
	{
		$this->stock += $stock;

        $this->stock = $this->stock > 0 ? $this->stock : 0;
        if($this->getManage()) {
            if ($this->stock > 0) {
                $this->setStatus(self::IN_STOCK);
            } else {
                $this->setStatus(self::OUT_STOCK);
            }
        }
	}

	/**
	 * @return string Backorders status.
	 */
	public function getAllowBackorders()
	{
		return $this->allowBackorders;
	}

	/**
	 * @return boolean Is product's stock managed by Jigoshop?
	 */
	public function getManage()
	{
		return $this->manage;
	}

	/**
	 * @return int Number of product's already sold.
	 */
	public function getSoldQuantity()
	{
		return $this->soldQuantity;
	}

	/**
	 * @return int Product status.
	 */
	public function getStatus()
	{
		return $this->status;
	}

	/**
	 * @return int Number of product's in stock.
	 */
	public function getStock()
	{
		return $this->stock;
	}

	/**
	 * (PHP 5 >= 5.1.0)<br/>
	 * String representation of object
	 *
	 * @link http://php.net/manual/en/serializable.serialize.php
	 * @return string the string representation of the object or null
	 */
	public function serialize()
	{
		return serialize([
			'manage' => $this->manage,
			'status' => $this->status,
			'allow_backorders' => $this->allowBackorders,
			'stock' => $this->stock,
        ]);
	}

	/**
	 * (PHP 5 >= 5.1.0)<br/>
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
		$this->manage = (bool)$data['manage'];
		$this->status = (int)$data['status'];
		$this->allowBackorders = $data['allow_backorders'];
		$this->stock = (int)$data['stock'];
	}

    /**
     * Used by json_encode method to proprly
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return [
            'manage' => $this->manage,
            'status' => $this->status,
            'allow_backorders' => $this->allowBackorders,
            'stock' => $this->stock,
        ];
    }
}
