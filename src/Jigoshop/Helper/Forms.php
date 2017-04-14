<?php

namespace Jigoshop\Helper;

use Jigoshop\Exception;
use Monolog\Registry;

class Forms
{
	protected static $checkboxTemplate = 'forms/checkbox';
	protected static $selectTemplate = 'forms/select';
	protected static $textTemplate = 'forms/text';
	protected static $numberTemplate = 'forms/number';
	protected static $constantTemplate = 'forms/constant';
	protected static $hiddenTemplate = 'forms/hidden';
	protected static $textareaTemplate = 'forms/textarea';
	protected static $daterangeTemplate = 'forms/daterange';

	/**
	 * Returns string for checkboxes if value is checked (value and current are the same).
	 *
	 * @param $value   string Value to check.
	 * @param $current string Value to compare.
	 *
	 * @return string
	 */
	public static function checked($value, $current)
	{
		if ($value == $current) {
			return ' checked="checked"';
		}

		return '';
	}

	/**
	 * Returns string for selects if value is within selected values.
	 *
	 * @param $value   string Value to check.
	 * @param $current string|array Currently selected values.
	 *
	 * @return string
	 */
	public static function selected($value, $current)
	{
		if ((is_array($current) && in_array($value, $current)) || $value == $current) {
			return ' selected="selected"';
		}

		return '';
	}

	/**
	 * Returns disabled string for inputs.
	 *
	 * @param $status bool Disable field.
	 *
	 * @return string
	 */
	public static function disabled($status)
	{
		if ($status) {
			return ' disabled="disabled"';
		}

		return '';
	}

	/**
	 * Outputs field based on specified type.
	 *
	 * @param $type  string Field type.
	 * @param $field array Field definition.
	 */
	public static function field($type, $field)
	{
		switch ($type) {
			case 'text':
				self::text($field);
				break;
			case 'select':
				self::select($field);
				break;
			case 'checkbox':
				self::checkbox($field);
				break;
			case 'textarea':
				self::textarea($field);
				break;
			case 'hidden':
				self::hidden($field);
				break;
			case 'constant':
				self::constant($field);
				break;
			case 'daterange':
				self::daterange($field);
				break;
			default :
				do_action('jigoshop\helper\forms\custom', $type, $field);
				break;
		}
	}

	/**
	 * Outputs simple text field.
	 *
	 * Available parameters (with defaults):
	 *   * id (null) - HTML id for the tag
	 *   * name (null) - HTML name for the tag
	 *   * type ('text') - HTML type for the tag
	 *   * label (null) - label for the tag
	 *   * value (false) - HTML value of the tag
	 *   * placeholder ('') - placeholder of the tag
	 *   * disabled (false) - whether checkbox is disabled
	 *   * classes (array()) - list of HTML classes for the tag
	 *   * description (false) - description of the tag
	 *   * tip (false) - tip for the tag
	 *   * hidden (false) - whether to hide element by default
	 *   * size (12) - default size of the element (Bootstrap column size 12)
	 *
	 * Field's name is required.
	 *
	 * @param $field array Field parameters.
	 *
	 * @throws \Jigoshop\Exception
	 */
	public static function text($field)
	{
		$defaults = [
			'id' => null,
			'name' => null,
			'type' => 'text',
			'label' => null,
			'value' => false,
			'placeholder' => '',
			'disabled' => false,
			'classes' => [],
			'description' => false,
			'tip' => false,
			'hidden' => false,
			'size' => 12,
        ];
		$field = wp_parse_args($field, $defaults);

		if (empty($field['name'])) {
			if (WP_DEBUG) {
				throw new Exception(sprintf('Field "%s" must have a name!', serialize($field)));
			}

			Registry::getInstance(JIGOSHOP_LOGGER)->addCritical('Field must have a name!', ['field' => $field]);

			return;
		}

		if (empty($field['id'])) {
			$field['id'] = self::prepareIdFromName($field['name']);
		}

		Render::output(static::$textTemplate, $field);
	}

	/**
	 * Outputs simple number field.
	 *
	 * Available parameters (with defaults):
	 *   * id (null) - HTML id for the tag
	 *   * name (null) - HTML name for the tag
	 *   * type ('number') - HTML type for the tag
	 *   * label (null) - label for the tag
	 *   * value (false) - HTML value of the tag
	 *   * placeholder ('') - placeholder of the tag
	 *   * disabled (false) - whether checkbox is disabled
	 *   * classes (array()) - list of HTML classes for the tag
	 *   * description (false) - description of the tag
	 *   * tip (false) - tip for the tag
	 *   * hidden (false) - whether to hide element by default
	 *   * size (12) - default size of the element (Bootstrap column size 12)
	 *   * min (false) - minimal value of number input
	 *   * max (false) - maximal value of number input
	 *   * step (1) - Step of number
	 *
	 * Field's name is required.
	 *
	 * @param $field array Field parameters.
	 *
	 * @throws \Jigoshop\Exception
	 */
	public static function number($field)
	{
		$defaults = [
			'id' => null,
			'name' => null,
			'type' => 'number',
			'label' => null,
			'value' => false,
			'placeholder' => '',
			'disabled' => false,
			'classes' => [],
			'description' => false,
			'tip' => false,
			'hidden' => false,
			'size' => 12,
			'min' => false,
			'max' => false,
			'step' => 1,
        ];

		$field = wp_parse_args($field, $defaults);

		if (empty($field['name'])) {
			if (WP_DEBUG) {
				throw new Exception(sprintf('Field "%s" must have a name!', serialize($field)));
			}

			Registry::getInstance(JIGOSHOP_LOGGER)->addCritical('Field must have a name!', ['field' => $field]);

			return;
		}

		if (empty($field['id'])) {
			$field['id'] = self::prepareIdFromName($field['name']);
		}

		Render::output(static::$numberTemplate, $field);
	}

	/**
	 * Prepares field name to be used as field ID.
	 *
	 * @param $name string Name to prepare.
	 *
	 * @return string Prepared ID.
	 */
	public static function prepareIdFromName($name)
	{
		return str_replace(['[', ']'], ['_', ''], $name);
	}

	/**
	 * Outputs select field.
	 *
	 * Available parameters (with defaults):
	 *   * id (null) - HTML id for the tag
	 *   * name (null) - HTML name for the tag
	 *   * label (null) - label for the tag
	 *   * value (false) - HTML value of the tag
	 *   * multiple (false) - whether there are many checkboxes with the same name
	 *   * placeholder ('') - placeholder of the tag
	 *   * disabled (false) - whether checkbox is disabled
	 *   * classes (array()) - list of HTML classes for the tag
	 *   * description (false) - description of the tag
	 *   * tip (false) - tip for the tag
	 *   * options (array) - available options to select
	 *   * hidden (false) - whether to hide element by default
	 *   * size (12) - default size of the element (Bootstrap column size 12)
	 *
	 * Field's name is required.
	 *
	 * @param $field array Field parameters.
	 *
	 * @throws \Jigoshop\Exception
	 */
	public static function select($field)
	{
		$defaults = [
			'id' => null,
			'name' => null,
			'label' => null,
			'value' => false,
			'multiple' => false,
			'placeholder' => '',
			'disabled' => false,
			'classes' => [],
			'description' => false,
			'tip' => false,
			'options' => [],
			'hidden' => false,
			'size' => 12,
            'args' => [],
        ];
		$field = wp_parse_args($field, $defaults);

		if (empty($field['name'])) {
			if (WP_DEBUG) {
				throw new Exception(sprintf('Field "%s" must have a name!', serialize($field)));
			}

			Registry::getInstance(JIGOSHOP_LOGGER)->addCritical('Field must have a name!', ['field' => $field]);

			return;
		}

		if (empty($field['id'])) {
			$field['id'] = self::prepareIdFromName($field['name']);
		}

		if ($field['multiple']) {
			$field['name'] .= '[]';
		}

		$field['description'] = esc_html($field['description']);

		// Support simple format for options
		if (!empty($field['options'])) {
			$firstElement = reset($field['options']);

			if (!is_array($firstElement)) {
				foreach ($field['options'] as $option => $label) {
					$field['options'][$option] = ['label' => $label];
				}
			} else if (!isset($firstElement['label']) && !isset($firstElement['items'])) { // TODO: Is this sufficient?
				foreach ($field['options'] as $option => $items) {
					foreach ($items as $suboption => $sublabel) {
						$field['options'][$option]['items'][$suboption] = ['label' => $sublabel];
					}
				}
			}
		}

		Render::output(static::$selectTemplate, $field);
	}

	/**
	 * Outputs checkbox field.
	 *
	 * Available parameters (with defaults):
	 *   * id (null) - HTML id for the tag
	 *   * name (null) - HTML name for the tag
	 *   * label (null) - label for the tag
	 *   * value ('on') - HTML value of the tag
	 *   * multiple (false) - whether there are many checkboxes with the same name
	 *   * checked (false) - whether checkbox is checked by default
	 *   * disabled (false) - whether checkbox is disabled
	 *   * classes (array()) - list of HTML classes for the tag
	 *   * description (false) - description of the tag
	 *   * tip (false) - tip for the tag
	 *   * hidden (false) - whether to hide element by default
	 *   * size (12) - default size of the element (Bootstrap column size 12)
	 *
	 * Field's name is required.
	 *
	 * @param $field array Field parameters.
	 *
	 * @throws \Jigoshop\Exception
	 *
	 */
	public static function checkbox($field)
	{
		$defaults = [
			'id' => null,
			'name' => null,
			'label' => null,
			'value' => 'on',
			'multiple' => false,
			'checked' => false,
			'disabled' => false,
			'classes' => [],
			'description' => false,
			'tip' => false,
			'hidden' => false,
			'size' => 12,
        ];
		$field = wp_parse_args($field, $defaults);

		if (empty($field['name'])) {
			if (WP_DEBUG) {
				throw new Exception('Field "%s" must have a name!', serialize($field));
			}

			Registry::getInstance(JIGOSHOP_LOGGER)->addCritical('Field must have a name!', ['field' => $field]);

			return;
		}

		if (empty($field['id'])) {
			$field['id'] = self::prepareIdFromName($field['name']);
		}

		if ($field['multiple']) {
			$field['name'] .= '[]';
		}

		Render::output(static::$checkboxTemplate, $field);
	}

	/**
	 * Outputs textarea field.
	 *
	 * Available parameters (with defaults):
	 *   * id (null) - HTML id for the tag
	 *   * name (null) - HTML name for the tag
	 *   * label (null) - label for the tag
	 *   * value (false) - HTML value of the tag
	 *   * rows (3) - HTML rows of the tag
	 *   * disabled (false) - whether checkbox is disabled
	 *   * classes (array()) - list of HTML classes for the tag
	 *   * description (false) - description of the tag
	 *   * tip (false) - tip for the tag
	 *   * hidden (false) - whether to hide element by default
	 *   * size (12) - default size of the element (Bootstrap column size 12)
	 *
	 * Field's name is required.
	 *
	 * @param $field array Field parameters.
	 *
	 * @throws \Jigoshop\Exception
	 */
	public static function textarea($field)
	{
		$defaults = [
			'id' => null,
			'name' => null,
			'label' => null,
			'value' => false,
			'rows' => 3,
			'disabled' => false,
			'classes' => [],
			'description' => false,
			'tip' => false,
			'hidden' => false,
			'size' => 12,
        ];
		$field = wp_parse_args($field, $defaults);

		if (empty($field['name'])) {
			if (WP_DEBUG) {
				throw new Exception(sprintf('Field "%s" must have a name!', serialize($field)));
			}

			Registry::getInstance(JIGOSHOP_LOGGER)->addCritical('Field must have a name!', ['field' => $field]);

			return;
		}

		if (empty($field['id'])) {
			$field['id'] = self::prepareIdFromName($field['name']);
		}

		Render::output(static::$textareaTemplate, $field);
	}

	/**
	 * Outputs simple text field.
	 *
	 * Available parameters (with defaults):
	 *   * id (null) - HTML id for the tag
	 *   * name (null) - array of HTML names for the tag
	 *   * type ('text') - HTML type for the tag
	 *   * label (null) - label for the tag
	 *   * value (false) - array of HTML values of the tag
	 *   * placeholder ('') - placeholder of the tag
	 *   * disabled (false) - whether checkbox is disabled
	 *   * classes (array()) - list of HTML classes for the tag
	 *   * description (false) - description of the tag
	 *   * tip (false) - tip for the tag
	 *   * size (12) - default size of the element (Bootstrap column size 12)
	 *
	 * Field's name is required.
	 *
	 * @param $field array Field parameters.
	 *
	 * @throws \Jigoshop\Exception
	 */
	public static function daterange($field)
	{
		$defaults = [
			'id' => null,
			'name' => null,
			'type' => 'text',
			'label' => null,
			'value' => false,
			'placeholder' => '',
			'classes' => [],
			'description' => false,
			'tip' => false,
			'size' => 12,
			'startDate' => false,
			'endDate' => false,
        ];
		$field = wp_parse_args($field, $defaults);

		if (empty($field['name'])) {
			if (WP_DEBUG) {
				throw new Exception(sprintf('Field "%s" must have a name!', serialize($field)));
			}

			Registry::getInstance(JIGOSHOP_LOGGER)->addCritical('Field must have a name!', ['field' => $field]);

			return;
		}

		Render::output(static::$daterangeTemplate, $field);
	}


	/**
	 * Outputs hidden field.
	 *
	 * Available parameters (with defaults):
	 *   * id (null) - HTML id for the tag
	 *   * name (null) - HTML name for the tag
	 *   * value (false) - HTML value of the tag
	 *   * classes (array()) - list of HTML classes for the tag
	 *
	 * Field's name is required.
	 *
	 * @param $field array Field parameters.
	 *
	 * @throws \Jigoshop\Exception
	 */
	public static function hidden($field)
	{
		$defaults = [
			'id' => null,
			'name' => null,
			'value' => false,
			'classes' => [],
        ];
		$field = wp_parse_args($field, $defaults);

		if (empty($field['name'])) {
			if (WP_DEBUG) {
				throw new Exception(sprintf('Field "%s" must have a name!', serialize($field)));
			}

			Registry::getInstance(JIGOSHOP_LOGGER)->addCritical('Field must have a name!', ['field' => $field]);

			return;
		}

		if (empty($field['id'])) {
			$field['id'] = self::prepareIdFromName($field['name']);
		}

		Render::output(static::$hiddenTemplate, $field);
	}

	/**
	 * Outputs simple static (constant) field.
	 *
	 * Available parameters (with defaults):
	 *   * id (null) - HTML id for the tag
	 *   * name (null) - HTML name for the tag
	 *   * label (null) - label for the tag
	 *   * value (false) - HTML value of the tag
	 *   * placeholder ('') - placeholder of the tag
	 *   * classes (array()) - list of HTML classes for the tag
	 *   * description (false) - description of the tag
	 *   * tip (false) - tip for the tag
	 *   * hidden (false) - whether to hide element by default
	 *   * size (12) - default size of the element (Bootstrap column size 12)
	 *
	 * Field's name is required.
	 *
	 * @param $field array Field parameters.
	 *
	 * @throws \Jigoshop\Exception
	 */
	public static function constant($field)
	{
		$defaults = [
			'id' => null,
			'name' => null,
			'label' => null,
			'value' => false,
			'placeholder' => '',
			'classes' => [],
			'description' => false,
			'tip' => false,
			'hidden' => false,
			'size' => 11,
			'startDate' => false,
			'endDate' => false,
        ];
		$field = wp_parse_args($field, $defaults);

		if (empty($field['name'])) {
			if (WP_DEBUG) {
				throw new Exception(sprintf('Field "%s" must have a name!', serialize($field)));
			}

			Registry::getInstance(JIGOSHOP_LOGGER)->addCritical('Field must have a name!', ['field' => $field]);

			return;
		}

		if (empty($field['id'])) {
			$field['id'] = self::prepareIdFromName($field['name']);
		}

		Render::output(static::$constantTemplate, $field);
	}

	public static function printHiddenFields($fields, $exceptions = [])
	{
		foreach ($fields as $key => $value) {
			if (!in_array($key, $exceptions)) {
				if (is_array($value)) {
					foreach ($value as $subkey => $subvalue) {
						echo '<input type="hidden" name="'.$key.'['.$subkey.']" value="'.$value.'" />';
					}
				} else {
					echo '<input type="hidden" name="'.$key.'" value="'.$value.'" />';
				}
			}
		}
	}
}
