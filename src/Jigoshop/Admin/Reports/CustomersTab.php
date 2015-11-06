<?php

namespace Jigoshop\Admin\Reports;

class CustomersTab implements TabInterface
{
	const SLUG = 'customers';

	public function __construct()
	{

	}

	/**
	 * @return string Title of the tab.
	 */
	public function getTitle()
	{
		return __('Customers', 'jigoshop');
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