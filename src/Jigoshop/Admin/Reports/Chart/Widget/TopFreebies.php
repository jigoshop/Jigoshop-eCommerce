<?php

namespace Jigoshop\Admin\Reports\Chart\Widget;

use Jigoshop\Admin\Reports\Chart\WidgetInterface;
use Jigoshop\Helper\Render;

class TopFreebies implements WidgetInterface
{
	const SLUG = 'top_freebies';
	private $topFreebies;

	public function __construct($topFreebies)
	{
		$this->topFreebies = $topFreebies;
	}

	public function getSlug()
	{
		return self::SLUG;
	}

	public function getTitle()
	{
		return __('Top Freebies', 'jigoshop');
	}

	public function getArgs()
	{
		$args = [];
		foreach($this->topFreebies as $product){
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
		Render::output('admin/reports/widget/top_freebies', ['args' => $this->getArgs()]);
	}
}