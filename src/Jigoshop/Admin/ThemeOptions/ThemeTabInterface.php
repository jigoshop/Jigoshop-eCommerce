<?php
namespace Jigoshop\Admin\ThemeOptions;

interface ThemeTabInterface {
	/**
	 * Returns theme tab slug.
	 * 
	 * @return string Theme tab slug.
	 */
	public function getSlug();

	/**
	 * Returns theme tab title.
	 * 
	 * @return string Theme tab title.
	 */
	public function getTitle();

	/**
	 * Returns array of sections with fields to be displayed in a tab.
	 * 
	 * @return array Sections with fields.
	 */
	public function getSections();

	/**
	 * Validates and sanitizes theme options.
	 * 
	 * @param array $options Options to sanitize/validate.
	 * 
	 * @return array Sanitized options.
	 */
	public function validate($options);
}