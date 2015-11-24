<?php
/**
 * Created by PhpStorm.
 * User: Borbis Media
 * Date: 2015-11-23
 * Time: 09:00
 */

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
		return __('Custom Range', 'jigoshop');
	}

	public function getArgs()
	{
		$args = array(
			'start_date' => isset($_GET['start_date']) ? $_GET['start_date'] : '',
			'end_date' => isset($_GET['end_date']) ? $_GET['end_date'] : '',
		);

		return $args;
	}

	public function display()
	{
		Render::output('admin/reports/widget/custom_range', array(
			'args' => $this->getArgs()
		));
	}
}