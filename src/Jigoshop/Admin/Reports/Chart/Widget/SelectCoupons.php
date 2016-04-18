<?php

namespace Jigoshop\Admin\Reports\Chart\Widget;

use Jigoshop\Admin\Helper\Forms;
use Jigoshop\Admin\Reports\Chart\WidgetInterface;

class SelectCoupons implements WidgetInterface
{
	const SLUG = 'select_coupons';
	private $selectedCoupons = array();
	private $allUsedCoupons = array();

	public function __construct($selectedCoupons, $allUsedCoupons)
	{
		$this->selectedCoupons = $selectedCoupons;
		$this->allUsedCoupons = array_map(function($coupon){
			return $coupon['code'];
		}, $allUsedCoupons);
	}

	public function getSlug()
	{
		return self::SLUG;
	}

	public function getTitle()
	{
		return __('Select Coupons', 'jigoshop');
	}

	public function getArgs()
	{
		return array(
			'id' => 'select_coupons',
			'name' => 'coupon_codes',
			'value' => $this->selectedCoupons,
			'multiple' => true,
			'classes' => array(),
			'options' => $this->allUsedCoupons,
			'size' => 14,
		);
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