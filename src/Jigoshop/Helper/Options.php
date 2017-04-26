<?php

namespace Jigoshop\Helper;

/**
 * Class Options
 * @package Jigoshop\Helper;
 * @author Krzysztof Kasowski
 */
class Options
{
    /** @var \Jigoshop\Core\Options */
    private static $options;
    /** @var array */
    private static $defaults;
    /** @var  array */
    private static $loadedOptions;

    /**
     * @param Options $options Options object.
     */
    public static function setOptions($options)
    {
        static::$options = $options;
    }

    /**
     * @param string $id
     * @param array $defaults
     */
    public static function setDefaults($id, array $defaults)
    {
        self::$defaults[$id] = $defaults;
    }

    /**
     * @param string $id
     * @return array
     */
    public static function getOptions($id)
    {
        if(!isset(self::$loadedOptions[$id])) {
            $defaults = [];
            if (isset(self::$defaults[$id])) {
                $defaults = self::$defaults[$id];
            }

            if(self::$options) {
                self::$loadedOptions[$id] = array_merge($defaults, self::$options->get($id, []));
            } else {
                self::$loadedOptions[$id] = $defaults;
            }
        }

        return self::$loadedOptions[$id];
    }

    /**
     * @param string $id
     */
    public static function clear($id)
    {
        if(isset(self::$loadedOptions[$id])) {
            self::$loadedOptions[$id] = [];
        }
    }
}