<?php

namespace Jigoshop\Admin\Reports\Chart\Widget;

use Jigoshop\Admin\Reports\Chart\WidgetInterface;
use Jigoshop\Entity\Order\Discount\Type;
use Jigoshop\Helper\Product;
use Jigoshop\Helper\Render;

class MostDiscount implements WidgetInterface
{
	const SLUG = 'most_discount_%s';
	private $type;
	private $mostDiscount;

	public function __construct($type, $mostDiscount)
	{
	    $this->type = $type;
		$this->mostDiscount = $mostDiscount;
	}

	public function getSlug()
	{
		return sprintf(self::SLUG, $this->type);
	}

	public function getTitle()
	{
		return sprintf(__('Most Discount %s Codes', 'jigoshop-ecommerce'), Type::getName($this->type));
	}

	public function getArgs()
	{
		$args = [];
		foreach($this->mostDiscount as $data){
			$args[] = [
				'total' => Product::formatPrice($data['amount']),
				'url' => esc_url(add_query_arg('codes['.$this->type.']', $data['code'], add_query_arg('last_used', self::SLUG))),
				'title' => $data['code']
            ];
		}

		return $args;
	}

	public function isVisible()
	{
		return (isset($_GET['last_used']) && $_GET['last_used'] == self::SLUG);
	}
	
	public function display()
	{
		Render::output('admin/reports/widget/most_discount', ['args' => $this->getArgs()]);
	}
}