<?php

namespace Jigoshop\Entity\Product\Attribute;

use Jigoshop\Container;
use Jigoshop\Entity\JsonInterface;
use Jigoshop\Entity\Product\Attribute;

class Option implements JsonInterface
{
	/** @var int */
	private $id;
	/** @var string */
	private $label;
	/** @var mixed */
	private $value;
	/** @var Attribute */
	private $attribute;

	/**
	 * @return int Option ID.
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @param int $id New ID for option.
	 */
	public function setId($id)
	{
		$this->id = $id;
	}

	/**
	 * @return string Option label.
	 */
	public function getLabel()
	{
		return $this->label;
	}

	/**
	 * @param string $label New label.
	 */
	public function setLabel($label)
	{
		$this->label = $label;
	}

	/**
	 * @return mixed Option value.
	 */
	public function getValue()
	{
		return $this->value;
	}

	/**
	 * @param mixed $value New value.
	 */
	public function setValue($value)
	{
		$this->value = $value;
	}

	/**
	 * @return Attribute Associated attribute.
	 */
	public function getAttribute()
	{
		return $this->attribute;
	}

	/**
	 * @param Attribute $attribute Attribute to attach option to.
	 */
	public function setAttribute($attribute)
	{
		$this->attribute = $attribute;
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
            'label' => $this->label,
            'value' => $this->value,
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
        if(isset($json['label'])) {
            $this->label = $json['label'];
        }
        if(isset($json['value'])) {
            $this->value = $json['value'];
        }
    }
}
