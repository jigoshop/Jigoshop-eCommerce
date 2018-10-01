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
    protected static $userDefinedTemplate = 'forms/userDefined';

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
            case 'number':
                self::number($field);
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
            case 'user_defined':
                self::userDefined($field);
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
     *     * data (array()) - key-value pairs for data attributes
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
            'data' => []
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
     *   * data (array()) - key-value pairs for data attributes
     *
     * Field's name is required.
     *
     * @param $field array Field parameters.
     * @param $type string type of number field
     * @throws \Jigoshop\Exception
     */
    public static function number($field, $type = "int")
    {
        switch ($type) {
            case "int":
                $field = self::integer($field);
                break;
            case "float":
                $field = self::float($field);
                break;
            case "currency":
                $field = self::currency($field);
                break;
            default:
                $field = self::integer($field);
                break;
        }
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

    public static function float($field)
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
            'step' => 0.01,
            'data' => []
        ];

        $field = wp_parse_args($field, $defaults);

        return $field;
    }

    public static function currency($field)
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
            'step' => pow(0.1, Currency::decimals()),
            'data' => []
        ];

        $field = wp_parse_args($field, $defaults);

        return $field;
    }

    public static function integer($field)
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
            'data' => []
        ];

        $field = wp_parse_args($field, $defaults);

        return $field;


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
     *   * data (array()) - key-value pairs for data attributes
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
            'data' => []
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

        foreach ($field['options'] as $value => $data) {
            if (is_array($data)) {
                if (isset($data['label']) && !isset($data['items'])) {
                    continue;
                }
                $field['options'][$value] = [
                    'label' => isset($data['label']) ? $data['label'] : $value,
                    'items' => isset($data['items']) ? $data['items'] : $data,
                ];
                foreach ($field['options'][$value]['items'] as $suboption => $subdata) {
                    if (!is_array($subdata)) {
                        $field['options'][$value]['items'][$suboption] = ['label' => $subdata];
                    }
                }
            } else {
                $field['options'][$value] = ['label' => $data];
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
     *   * data (array()) - key-value pairs for data attributes
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
            'data' => []
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
     *   * data (array()) - key-value pairs for data attributes
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
            'data' => []
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
     *   * data (array()) - key-value pairs for data attributes
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
            'data' => [
                'from' => [],
                'to' => []
            ]
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
     *   * data (array()) - key-value pairs for data attributes
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
            'data' => []
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
     *   * data (array()) - key-value pairs for data attributes
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
            'data' => []
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

    /**
     * Outputs already rendered field.
     *
     * Available parameters (with defaults):
     *   * name (null) - name for the tag
     *   * label (null) - label for the tag
     *   * tip (false) - tip for the tag
     *   * display (null) - callback for display content
     *
     * @param $field array Field parameters.
     *
     * @throws \Jigoshop\Exception
     */
    public static function userDefined($field)
    {
        $defaults = [
            'name' => '',
            'title' => '',
            'label' => '',
            'tip' => '',
            'display' => '',
            'size' => 12
        ];

        $field = wp_parse_args($field, $defaults);
        if (isset($field['display']) && is_callable($field['display'])) {
            $result = call_user_func($field['display'], $field);

            Render::output(static::$userDefinedTemplate, [
                'title' => isset($field['title']) ? $field['title'] : '',
                'label' => isset($field['label']) ? $field['label'] : '',
                'tip' => isset($field['tip']) ? $field['tip'] : '',
                'size' => $field['size'],
                'display' => $result
            ]);
        }
    }

    public static function printHiddenFields($fields, $exceptions = [])
    {
        foreach ($fields as $key => $value) {
            if (!in_array($key, $exceptions)) {
                if (is_array($value)) {
                    foreach ($value as $subkey => $subvalue) {
                        echo '<input type="hidden" name="' . $key . '[' . $subkey . ']" value="' . $value . '" />';
                    }
                } else {
                    echo '<input type="hidden" name="' . $key . '" value="' . $value . '" />';
                }
            }
        }
    }
}
