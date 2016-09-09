<?php

namespace Jigoshop\Admin\Reports\Chart\Widget;

use Jigoshop\Admin\Reports\Chart\WidgetInterface;
use Jigoshop\Helper\Product;
use Jigoshop\Helper\Render;

class MostDiscount implements WidgetInterface
{
	const SLUG = 'most_discount';
	private $mostDiscount;

	public function __construct($mostDiscount)
	{
		$this->mostDiscount = $mostDiscount;
	}

	public function getSlug()
	{
		return self::SLUG;
	}

	public function getTitle()
	{
		return __('Most Discount', 'jigoshop');
	}

	public function getArgs()
	{
		$args = array();
		foreach($this->mostDiscount as $coupon){
			$args[] = array(
				'total' => Product::formatPrice($coupon['amount']),
				'url' => esc_url(add_query_arg('coupon_codes', $coupon['code'], add_query_arg('last_used', self::SLUG))),
				'title' => $coupon['code']
			);
		}

		return $args;
	}

	public function isVisible()
	{
		return (isset($_GET['last_used']) && $_GET['last_used'] == self::SLUG);
	}
	
	public function display()
	{
		Render::output('admin/reports/widget/most_discount', array('args' => $this->getArgs()));
	}
}