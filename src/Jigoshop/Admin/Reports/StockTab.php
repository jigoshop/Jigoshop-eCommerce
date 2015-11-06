<?php

namespace Jigoshop\Admin\Reports;

class StockTab implements TabInterface
{
	const SLUG = 'stock';

	public function __construct()
	{

	}

	/**
	 * @return string Title of the tab.
	 */
	public function getTitle()
	{
		return __('Stock', 'jigoshop');
	}

	/**
	 * @return string Tab slug.
	 */
	public function getSlug()
	{
		return self::SLUG;
	}

	/**
	 * @return array List of items to display.
	 */
	public function display()
	{}
}