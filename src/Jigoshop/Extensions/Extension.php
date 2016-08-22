<?php

namespace Jigoshop\Extensions;

use Jigoshop\Container\Configurations\Configuration;
use Jigoshop\Extensions;

/**
 * Class Extension
 * @package Jigoshop\Extensions;
 * @author Krzysztof Kasowski
 */
abstract class Extension
{
    /** @var  Extension  */
    private static $instance;
    /** @var  Extensions\Extension\Plugin  */
    private $plugin;
    /** @var  Extensions\Extension\Render  */
    private $render;

    /**
     * Extension constructor.
     * @param string $name
     * @param string $fileName
     */
    public function __construct($name, $fileName)
    {
        $this->plugin = new Extensions\Extension\Plugin($name, $fileName);
        $this->render = new Extensions\Extension\Render($this->getTemplateDirBaseName(), $this->getPlugin()->getDir());
    }

    /**
     * @param Extension $extension
     */
    public static function setInstance(Extension $extension)
    {
        self::$instance = $extension;
    }

    /**
     * @return Extension
     */
    public static function getInstance()
    {
        return self::$instance;
    }

    /**
     * @return Extension\Plugin
     */
    public function getPlugin()
    {
        return $this->plugin;
    }

    public function getRender()
    {
        return $this->render;
    }

    /**
     * @return string
     */
    public function getDir()
    {
        return __DIR__;
    }

    /**
     * @return string
     */
    public function getNamespace()
    {
        return __NAMESPACE__;
    }

    /**
     * @return string
     */
    abstract public function getTemplateDirBaseName();

    /**
     * @return Configuration
     */
    abstract public function getConfiguration();
}