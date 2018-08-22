<?php

namespace Jigoshop\Extensions\Extension;

/**
 * Class Render
 * @package Jigoshop\Extensions\Extension;
 * @author Krzysztof Kasowski
 */
class Render
{
    private $location;
    private $templateDir;

    /**
     * Render constructor.
     * @param $location
     * @param $templateDir
     */
    public function __construct($location, $templateDir)
    {
        $this->location = $location;
        $this->templateDir = $templateDir;
    }

    /**
     * Returns rendered HTML.
     *
     * @param string $template    Template to render.
     * @param array  $environment Variables to make available to the template
     *
     * @return string Rendered HTML.
     */
    public function get($template, array $environment)
    {
        ob_start();
        $this->output($template, $environment);

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
        $file = $this->locateTemplate($template);
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
        $file = locate_template(['jigoshop/'.$this->templateDir.'/'.$template.'.php'], false, false);
        if (empty($file)) {
            $file = $this->location.'/templates/'.$template.'.php';
        }

        return $file;
    }
}