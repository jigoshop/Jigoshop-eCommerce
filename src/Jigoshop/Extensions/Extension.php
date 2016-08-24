<?php

namespace Jigoshop\Extensions;

use Jigoshop\Container\Configurations\Configuration;
use Jigoshop\Container\Configurations\ConfigurationInterface;
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
    /** @var  \ReflectionClass */
    private $reflection;

    /**
     * Extension constructor.
     * @param string $fileName
     */
    public function __construct($fileName)
    {
        $this->plugin = new Extensions\Extension\Plugin($fileName);
        $this->reflection = new \ReflectionClass($this);
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

    /**
     * @return Extension\Render
     */
    public function getRender()
    {
        return $this->render;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return dirname($this->reflection->getFileName()).'/'.$this->reflection->getShortName();
    }

    /**
     * @return string
     */
    public function getNamespace()
    {
        return $this->reflection->getName();
    }

    /**
     * @return ConfigurationInterface
     */
    abstract public function getConfiguration();
}