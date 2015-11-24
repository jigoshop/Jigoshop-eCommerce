<?php

namespace Jigoshop\Admin\Reports\Chart\Widget;

use Jigoshop\Admin\Reports\Chart\WidgetInterface;
use Jigoshop\Helper\Render;

class TopSellers implements WidgetInterface
{
	const SLUG = 'top_sellers';
	private $topSelers;

	public function __construct($topSellers)
	{
		$this->topSelers = $topSellers;
	}

	public function getSlug()
	{
		return self::SLUG;
	}

	public function getTitle()
	{
		return __('Top Sellers', 'jigoshop');
	}

	public function getArgs()
	{
		$args = array();
		foreach($this->topSelers as $product){
			$args[] = array(
				'count' => $product->order_item_qty,
				'id' => $product->product_id,
				'url' => esc_url(add_query_arg('product_ids', $product->product_id)),
				'title' => get_the_title($product->product_id)
			);
		}

		return $args;
	}

	public function display()
	{
		Render::output('admin/reports/widget/top_sellers', array('args' => $this->getArgs()));
	}
}