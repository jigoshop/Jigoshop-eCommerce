<?php

namespace Jigoshop\Entity\Product\Attribute;

use Jigoshop\Entity\Product\Attribute;

class Select extends Attribute
{
	const TYPE = 1;

	public function __construct($exists = false)
	{
		parent::__construct($exists);
	}

	/**
	 * @return int Type of attribute.
	 */
	public function getType()
	{
		return self::TYPE;
	}

	/**
	 * @param mixed $value New value for attribute.
	 */
	public function setValue($value)
	{
		$this->value = $value;
	}

	/**
	 * @return string Value of attribute to be printed.
	 */
	public function printValue()
	{
	    $options = $this->options;
	    $value = $this->value;

	    echo array_reduce($options, function($carry, $option) use ($value) {
		    /** @var $option Option */
            return $option->getId() == $value ? $option->getLabel() : $carry;
        });
	}
}
