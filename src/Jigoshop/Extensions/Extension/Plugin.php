<?php

namespace Jigoshop\Extensions\Extension;

use Jigoshop\Exception;

/**
 * Class Plugin
 * @package Jigoshop\Extensions\Extension;
 * @author Krzysztof Kasowski
 */
class Plugin
{
    /** @var  int  */
    private $id;
    /** @var  string  */
    private $name;
    /** @var  string  */
    private $description;
    /** @var  string  */
    private $requiredVersion;
    /** @var  string */
    private $version;
    /** @var  string */
    private $author;
    /** @var  string  */
    private $authorUrl;
    /** @var  string  */
    private $templateDir;
    /** @var string  */
    private $dir;
    /** @var string  */
    private $url;
    /** @var string  */
    private $basename;

    /**
     * Plugin constructor.
     * @param $filename
     */
    public function __construct($filename)
    {
        $this->dir = dirname($filename);
        $this->url = plugins_url('', $filename);
        $this->basename = plugin_basename($filename);
        $data = $this->getDataFromPluginDatafile();
        $this->name = $data->name;
        $this->description = $data->description;
        $this->requiredVersion = $data->requiredVersion;
        $this->id = $data->id;
        $this->version = $data->version;
    }

    private function getDataFromPluginDatafile()
    {
        $file = $this->dir.'/jigoshop.json';
        if(file_exists($file)) {
            return json_decode(file_get_contents($file));
        }
        throw new Exception(sprintf('Plugin datafile [%s] does not exist', $file));
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

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return string
     */
    public function getRequiredVersion()
    {
        return $this->requiredVersion;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return v
     */
    public function getVersion()
    {
        return $this->version;
    }
}