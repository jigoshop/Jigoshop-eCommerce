<?php

namespace Jigoshop\Integration;

use Jigoshop\Exception;

/**
 * Class Render
 * @package Integration\Helper;
 * @author Krzysztof Kasowski
 */
class Render
{
    /** @var array  */
    protected static $locations = [];

    /**
     * @param $key
     * @param $path
     */
    public static function addLocation($key, $path)
    {
        if(!isset(self::$locations[$key])) {
            self::$locations[$key] = $path;
        } else {
            throw new Exception(sprintf(__('The key [%s] already exists.', 'jigoshop-ecommerce'), $key));
        }
    }

    /**
     * Returns rendered HTML.
     *
     * @param string $key         Location of template files.
     * @param string $template    Template to render.
     * @param array  $environment Variables to make available to the template
     *
     * @return string Rendered HTML.
     */
    public static function get($key, $template, array $environment)
    {
        ob_start();
        self::output($key, $template, $environment);

        return ob_get_clean();
    }

    /**
     * Outputs HTML template.
     *
     * @param string $key         Location of template files.
     * @param string $template    Template to render.
     * @param array  $environment Variables to make available to the template
     */
    public static function output($key, $template, array $environment)
    {
        $file = self::locateTemplate($key, $template);
        extract($environment);
        /** @noinspection PhpIncludeInspection */
        require($file);
    }

    /**
     * Locates template based on available sources - current theme directory, stylesheet directory and Jigoshop templates directory.
     *
     * @param string $key Location of template files.
     * @param string $template Template to find.
     *
     * @return string Path to located file.
     */
    public static function locateTemplate($key, $template)
    {
        $file = locate_template(['jigoshop/'.strtolower($key).'/'.$template.'.php'], false, false);
        if (empty($file)) {
            if(!isset(self::$locations[$key])) {
                throw new Exception(sprintf(__('The key [%s] does not exist.', 'jigoshop-ecommerce'), $key));
            }

            $file = self::$locations[$key].'/templates/'.$template.'.php';
        }

        return $file;
    }
}