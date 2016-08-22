<?php

namespace Jigoshop;

use Composer\Autoload\ClassLoader;
use Jigoshop\Extensions\Extension;

/**
 * Class Extensions
 * @package Jigoshop;
 * @author Krzysztof Kasowski
 */
class Extensions
{
    /** @var  Extension[]  */
    private static $extensions = array();
    /** @var  ClassLoader  */
    private $classLoader;

    /**
     * Extensions constructor.
     */
    public function __construct()
    {
    }

    /**
     * @param Extension $extentsion
     */
    public static function register(Extension $extentsion)
    {
        self::$extensions[] = $extentsion;
    }

    /**
     * @param ClassLoader $classLoader
     */
    public function setClassLoader(ClassLoader $classLoader)
    {
        $this->classLoader = $classLoader;
    }

    /**
     * @return Extension[]
     */
    public function getExtensions()
    {
        return self::$extensions;
    }

    /**
     * Init extensions
     */
    public function init()
    {
        foreach(self::$extensions as $extension) {
            $this->addPsr4Autoload($extension);
        }
    }

    /**
     * @param Extension $extension
     */
    public function addPsr4Autoload(Extension $extension)
    {
        $this->classLoader->addPsr4($extension->getNamespace().'\\', array($extension->getDir()));
    }
}