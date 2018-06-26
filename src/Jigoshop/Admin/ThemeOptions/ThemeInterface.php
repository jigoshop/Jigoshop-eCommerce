<?php
namespace Jigoshop\Admin\ThemeOptions;

interface ThemeInterface {
	/**
	 * Returns slug of theme.
	 * 
	 * @return string Theme slug.
	 */
	public function getSlug();

	/**
	 * Returns human-readable theme name.
	 * 
	 * @return string Theme name.
	 */
	public function getName();

	/**
	 * Returns tab objects of theme.
	 * 
	 * @return array Array of \Jigoshop\Admin\ThemeOptions\ThemeTab objects.
	 */
	public function getTabs();
}