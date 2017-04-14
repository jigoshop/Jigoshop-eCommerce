<?php

namespace Jigoshop\Extensions\Helper;

use Jigoshop\Extensions\Extension\Plugin;

/**
 * Class Render
 * @package Jigoshop\Extensions\Helper;
 * @author Krzysztof Kasowski
 */
class Render
{
    /**
     * Returns rendered HTML.
     *
     * @param Plugin $plugin      Plugin data object.
     * @param string $template    Template to render.
     * @param array  $environment Variables to make available to the template
     *
     * @return string Rendered HTML.
     */
    public static function get(Plugin $plugin, $template, array $environment)
    {
        ob_start();
        self::output($plugin, $template, $environment);

        return ob_get_clean();
    }

    /**
     * Outputs HTML template.
     *
     * @param Plugin $plugin      Plugin data object.
     * @param string $template    Template to render.
     * @param array  $environment Variables to make available to the template
     */
    public static function output(Plugin $plugin, $template, array $environment)
    {
        $file = self::locateTemplate($plugin, $template);
        extract($environment);
        /** @noinspection PhpIncludeInspection */
        require($file);
    }

    /**
     * Locates template based on available sources - current theme directory, stylesheet directory and Jigoshop templates directory.
     *
     * @param Plugin $plugin   Plugin data object.
     * @param string $template Template to find.
     *
     * @return string Path to located file.
     */
    public static function locateTemplate(Plugin $plugin, $template)
    {
        $file = locate_template(['jigoshop/'.$plugin->getTemplateDir().'/'.$template.'.php'], false, false);
        if (empty($file)) {
            $file = $plugin->getDir().'/templates/'.$template.'.php';
        }

        return $file;
    }
}