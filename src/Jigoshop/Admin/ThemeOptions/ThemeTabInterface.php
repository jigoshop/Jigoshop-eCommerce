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
	 * Returns assoc array with default options (names and default values).
	 * 
	 * @return array Default tab options.
	 */
	public function getDefaultOptions();

	/**
	 * Called by JSE core to populate ThemeTabInterface with previously saved options merged with default options.
	 * 
	 * @param array $options Previously saved tab options.
	 */
	public function setOptions($options);

	/**
	 * Validates and sanitizes theme options.
	 * 
	 * @param array $options Options to sanitize/validate.
	 * 
	 * @return array Sanitized options.
	 */
	public function validate($options);
}