<?php

namespace Jigoshop\Admin\Reports\Chart\Widget;

use Jigoshop\Admin\Reports\Chart\WidgetInterface;
use Jigoshop\Helper\Render;

class CustomRange implements WidgetInterface
{
	const SLUG = 'custom_range';

	public function __construct()
	{

	}

	public function getSlug()
	{
		return self::SLUG;
	}

	public function getTitle()
	{
		return __('Custom Range', 'jigoshop-ecommerce');
	}

	public function getArgs()
	{
		$args = [
			'start_date' => isset($_GET['start_date']) ? $_GET['start_date'] : '',
			'end_date' => isset($_GET['end_date']) ? $_GET['end_date'] : '',
        ];

		return $args;
	}

	public function isVisible()
	{
		return (!isset($_GET['last_used']) 
			|| (isset($_GET['start_date']) && !empty($_GET['start_date']))
			|| (isset($_GET['end_date']) && !empty($_GET['end_date'])) );
	}

	public function display()
	{
		Render::output('admin/reports/widget/custom_range', [
			'args' => $this->getArgs()
        ]);
	}
}