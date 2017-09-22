<?php

namespace Jigoshop\Admin\Reports\Chart\Widget;

use Jigoshop\Admin\Helper\Forms;
use Jigoshop\Admin\Reports\Chart\WidgetInterface;
use Jigoshop\Entity\Order\Discount\Type;

class SelectCodes implements WidgetInterface
{
	const SLUG = 'select_%s_codes';
	private $type;
	private $selectedCodes = [];
	private $allUsedCodes = [];

	public function __construct($type, $selectedCodes, $allUsedCodes)
	{
	    $this->type = $type;
		$this->selectedCodes = $selectedCodes;
		foreach($allUsedCodes as $code) {
		    $this->allUsedCodes[$code] = $code;
        }
	}

	public function getSlug()
	{
		return sprintf(self::SLUG, $this->type);
	}

	public function getTitle()
	{
		return sprintf(__('Select %s codes', 'jigoshop-ecommerce'), Type::getName($this->type));
	}

	public function getArgs()
	{
		return [
			'id' => $this->getSlug(),
			'name' => 'codes['.$this->type.']',
			'value' => $this->selectedCodes,
			'multiple' => true,
			'classes' => [],
			'options' => $this->allUsedCodes,
			'size' => 14,
        ];
	}

	public function isVisible()
	{
		return true;
	}
	
	public function display()
	{
		Forms::select($this->getArgs());
	}
}