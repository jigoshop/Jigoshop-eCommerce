<?php

namespace Jigoshop\Extensions\Extension;

/**
 * Class Plugin
 * @package Jigoshop\Extensions\Extension;
 * @author Krzysztof Kasowski
 */
class Plugin
{
    /** @var  string  */
    private $name;
    /** @var string  */
    private $dir;
    /** @var string  */
    private $url;
    /** @var string  */
    private $basename;

    /**
     * Plugin constructor.
     * @param $name
     * @param $filename
     */
    public function __construct($name, $filename)
    {
        $this->name = $name;
        $this->dir = dirname($filename);
        $this->url = plugins_url('', $filename);
        $this->basename = plugin_basename($filename);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getDir()
    {
        return $this->dir;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @return string
     */
    public function getBasename()
    {
        return $this->basename;
    }
}