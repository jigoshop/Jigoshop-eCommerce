<?php

namespace Jigoshop\Admin\Reports;

/**
 * Interface for sales reports tabs.
 *
 * @package Jigoshop\Admin\Settings
 */
interface TabInterface
{
	/**
	 * @return string Title of the tab.
	 */
	public function getTitle();

	/**
	 * @return string Tab slug.
	 */
	public function getSlug();

	/**
	 * @return array List of items to display.
	 */
	public function display();
}