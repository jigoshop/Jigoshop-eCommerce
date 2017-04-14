<?php

namespace Jigoshop\Entity\Product\Attributes;

/**
 * Product size.
 *
 * @package Jigoshop\Entity\Product\Attributes
 * @author  Amadeusz Starzykiewicz
 */
class Size implements \Serializable, \JsonSerializable
{
	/** @var float */
	private $width = 0.0;
	/** @var float */
	private $height = 0.0;
	/** @var float */
	private $length = 0.0;
	/** @var float */
	private $weight = 0.0;

	/**
	 * @param float $height New product height.
	 */
	public function setHeight($height)
	{
		$this->height = $height;
	}

	/**
	 * @param float $length New product length.
	 */
	public function setLength($length)
	{
		$this->length = $length;
	}

	/**
	 * @param float $weight New product weight.
	 */
	public function setWeight($weight)
	{
		$this->weight = $weight;
	}

	/**
	 * @param float $width New product width.
	 */
	public function setWidth($width)
	{
		$this->width = $width;
	}

	/**
	 * @return float
	 */
	public function getHeight()
	{
		return $this->height;
	}

	/**
	 * @return float
	 */
	public function getLength()
	{
		return $this->length;
	}

	/**
	 * @return float
	 */
	public function getWeight()
	{
		return $this->weight;
	}

	/**
	 * @return float
	 */
	public function getWidth()
	{
		return $this->width;
	}

	/**
	 * String representation of object.
	 *
	 * @link http://php.net/manual/en/serializable.serialize.php
	 * @return string the string representation of the object or null
	 */
	public function serialize()
	{
		return serialize([
			'weight' => $this->weight,
			'width' => $this->width,
			'height' => $this->height,
			'length' => $this->length,
        ]);
	}

	/**
	 * Constructs the object.
	 *
	 * @link http://php.net/manual/en/serializable.unserialize.php
	 *
	 * @param string $serialized The string representation of the object.
	 */
	public function unserialize($serialized)
	{
		$data = unserialize($serialized);
		$this->weight = (float)$data['weight'];
		$this->width = (float)$data['width'];
		$this->height = (float)$data['height'];
		$this->length = (float)$data['length'];
	}

    /**
     * Used by json_encode method to proprly
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return [
            'weight' => $this->weight,
            'width' => $this->width,
            'height' => $this->height,
            'length' => $this->length,
        ];
    }
}
