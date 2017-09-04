<?php

namespace Jigoshop\Admin\Reports\Chart\Widget;

use Jigoshop\Admin\Reports\Chart\WidgetInterface;
use Jigoshop\Entity\Order\Discount\Type;
use Jigoshop\Helper\Render;

class MostPopular implements WidgetInterface
{
	const SLUG = 'most_popular_%s';
	private $type;
	private $mostPopular;

	public function __construct($type,  $mostPopular)
	{
	    $this->type = $type;
		$this->mostPopular = $mostPopular;
	}

	public function getSlug()
	{
		return self::SLUG;
	}

	public function getTitle()
	{
		return sprintf(__('Most Popular %s Codes', 'jigoshop-ecommerce'), Type::getName($this->type));
	}

	public function getArgs()
	{
		$args = [];
		foreach($this->mostPopular as $coupon){
			$args[] = [
				'count' => $coupon['count'],
				'url' => esc_url(add_query_arg('codes['.$this->type.']', $coupon['code'], add_query_arg('last_used', self::SLUG))),
				'title' => $coupon['code']
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
		Render::output('admin/reports/widget/most_popular', ['args' => $this->getArgs()]);
	}
}