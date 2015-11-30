<?php

namespace Jigoshop\Admin\Reports\Chart\Widget;

use Jigoshop\Admin\Reports\Chart\WidgetInterface;
use Jigoshop\Helper\Render;

class MostPopular implements WidgetInterface
{
	const SLUG = 'most_popular';
	private $mostPopular;

	public function __construct($mostPopular)
	{
		$this->mostPopular = $mostPopular;
	}

	public function getSlug()
	{
		return self::SLUG;
	}

	public function getTitle()
	{
		return __('Most Popular', 'jigoshop');
	}

	public function getArgs()
	{
		$args = array();
		foreach($this->mostPopular as $coupon){
			$args[] = array(
				'count' => $coupon['usage'],
				'url' => esc_url(add_query_arg('coupon_codes', $coupon['code'])),
				'title' => $coupon['code']
			);
		}

		return $args;
	}

	public function display()
	{
		Render::output('admin/reports/widget/most_popular', array('args' => $this->getArgs()));
	}
}