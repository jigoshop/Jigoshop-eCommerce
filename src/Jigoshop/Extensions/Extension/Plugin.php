<?php

namespace Jigoshop\Extensions\Extension;

use Jigoshop\Core;
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
    /** @var  array  */
    private $data;

    /**
     * Plugin constructor.
     * @param $filename
     */
    public function __construct($filename)
    {
        $this->dir = dirname($filename);
        $this->url = plugins_url('', $filename);
        $this->basename = plugin_basename($filename);
        $this->data = $this->getDataFromPluginDatafile();

        $this->id = $this->data['id'];
        $this->name = $this->data['name'];
        $this->description = $this->data['description'];
        $this->requiredVersion = $this->data['requiredVersion'];
        $this->version = $this->data['version'];
        $this->author = $this->data['author'];
        $this->authorUrl = $this->data['authorUrl'];
        $this->templateDir = $this->data['templateDir'];
    }

    private function getDataFromPluginDatafile()
    {
        $file = $this->dir.'/plugin.json';
        if(file_exists($file)) {
            $defaults = array(
                'id' => '',
                'name' => '',
                'description' => '',
                'requiredVersion' => Core::VERSION,
                'version' => '1.0',
                'author' => 'Jigoshop ltd',
                'authorUrl' => 'https://www.jigoshop.com/',
                'templateDir' => ''
            );
            $data = json_decode(file_get_contents($file), true);

            return array_merge($defaults, $data);
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

    /**
     * @return string
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * @return string
     */
    public function getAuthorUrl()
    {
        return $this->authorUrl;
    }

    /**
     * @return string
     */
    public function getTemplateDir()
    {
        return $this->templateDir;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }
}