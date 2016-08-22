<?php

namespace Jigoshop\Extensions\Extension;

/**
 * Class Render
 * @package Jigoshop\Extensions\Extension;
 * @author Krzysztof Kasowski
 */
class Render
{
    /** @var  string  */
    private $dirBaseName;
    /** @var  string  */
    private $pluginDir;

    /**
     * Render constructor.
     * @param string $dirBaseName
     * @param string $pluginDir
     */
    public function __construct($dirBaseName, $pluginDir)
    {
        $this->dirBaseName = $dirBaseName;
    }

    /**
     * Returns rendered HTML.
     *
     * @param string $template    Template to render.
     * @param array  $environment Variables to make available to the template
     *
     * @return string Rendered HTML.
     */
    public function get( $template, array $environment)
    {
        ob_start();
        self::output($template, $environment);

        return ob_get_clean();
    }

    /**
     * Outputs HTML template.
     *
     * @param string $template    Template to render.
     * @param array  $environment Variables to make available to the template
     */
    public function output($template, array $environment)
    {
        $file = self::locateTemplate($template);
        extract($environment);
        /** @noinspection PhpIncludeInspection */
        require($file);
    }

    /**
     * Locates template based on available sources - current theme directory, stylesheet directory and Jigoshop templates directory.
     *
     * @param string $template Template to find.
     *
     * @return string Path to located file.
     */
    public function locateTemplate($template)
    {
        $file = locate_template(array('jigoshop/'.$this->dirBaseName.'/'.$template.'.php'), false, false);
        if (empty($file)) {
            $file = $this->pluginDir.'/templates/'.$template.'.php';
        }

        return $file;
    }
}