<?php

namespace Jigoshop\Admin\Reports\Chart\Widget;

use Jigoshop\Admin\Reports\Chart\WidgetInterface;
use Jigoshop\Helper\Product;
use Jigoshop\Helper\Render;

class TopEarners implements WidgetInterface
{
	const SLUG = 'top_earners';
	private $topEarners;

	public function __construct($topEarners)
	{
		$this->topEarners = $topEarners;
	}

	public function getSlug()
	{
		return self::SLUG;
	}

	public function getTitle()
	{
		return __('Top Earners', 'jigoshop-ecommerce');
	}

	public function getArgs()
	{
		$args = [];
		foreach($this->topEarners as $product){
			$args[] = [
				'total' => Product::formatPrice($product->price),
				'id' => $product->id,
				'url' => esc_url(add_query_arg('product_ids', $product->id, add_query_arg('last_used', self::SLUG))),
				'title' => $product->title
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
		Render::output('admin/reports/widget/top_earners', ['args' => $this->getArgs()]);
	}
}