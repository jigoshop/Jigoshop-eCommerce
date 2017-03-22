<?php

namespace Jigoshop\Entity\Order;

use Jigoshop\Entity\Order\Discount\Meta;

/**
 * Class Discount
 *
 * @package Jigoshop\Entity\Order
 * @author Krzysztof Kasowski
 */
class Discount implements \Serializable, \JsonSerializable
{
    /** @var  int */
    private $id;
    /** @var  string */
    private $code;
    /** @var  string  */
    private $type;
    /** @var  float */
    private $amount;
    /** @var Meta[] */
    private $meta = [];

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

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return float
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param float $amount
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
    }

    /**
     * Adds meta value to the item.
     *
     * @param Meta $meta Meta value to add.
     */
    public function addMeta(Meta $meta)
    {
        $meta->setDiscount($this);
        $this->meta[$meta->getKey()] = $meta;
    }

    /**
     * Removes meta value from the item and returns it.
     *
     * @param string $key Meta key.
     *
     * @return Meta Meta object.
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
     * @return Meta Meta object.
     */
    public function getMeta($key)
    {
        if (!isset($this->meta[$key])) {
            return null;
        }

        return $this->meta[$key];
    }

    /**
     * @return Meta[] All meta values assigned to the item.
     */
    public function getAllMeta()
    {
        return $this->meta;
    }

    /**
     * String representation of object
     * @link http://php.net/manual/en/serializable.serialize.php
     * @return string the string representation of the object or null
     * @since 5.1.0
     */
    public function serialize()
    {
        return serialize([
            'id' => $this->id,
            'code' => $this->code,
            'type' => $this->type,
            'amount' => $this->amount,
            'meta' => serialize($this->meta),
        ]);
    }

    /**
     * Constructs the object
     * @link http://php.net/manual/en/serializable.unserialize.php
     * @param string $serialized <p>
     * The string representation of the object.
     * </p>
     * @return void
     * @since 5.1.0
     */
    public function unserialize($serialized)
    {
        $data = unserialize($serialized);
        $this->id = $data['id'];
        $this->code = $data['code'];
        $this->type = $data['type'];
        $this->amount = $data['amount'];
        $this->meta = unserialize($data['meta']);

        foreach ($this->meta as $meta) {
            $meta->setDiscount($this);
        }
    }

    /**
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'type' => $this->type,
            'amount' => $this->amount,
            'meta' => array_values($this->meta),
        ];
    }
}