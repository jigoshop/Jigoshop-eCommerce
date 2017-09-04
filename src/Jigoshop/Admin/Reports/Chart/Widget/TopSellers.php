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
		return __('Top Sellers', 'jigoshop-ecommerce');
	}

	public function getArgs()
	{
		$args = [];
		foreach($this->topSelers as $product){
			$args[] = [
				'count' => $product->count,
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
		Render::output('admin/reports/widget/top_sellers', ['args' => $this->getArgs()]);
	}
}