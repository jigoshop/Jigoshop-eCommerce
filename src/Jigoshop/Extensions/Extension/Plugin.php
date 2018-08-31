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
     * @param $data
     */
    public function __construct($data)
    {
        $this->validateData($data);
        $this->fetch($data);
    }

    /**
     * @param array $data
     */
    private function validateData($data)
    {
        if(!isset($data['name'])) {
            throw new Exception(__('Plugin name was not specified', 'jigoshop-ecommerce'));
        }

        if(!isset($data['pluginFile'])) {
            throw new Exception(sprintf(__('Plugin file path was not specified in %s', 'jigoshop-ecommerce'), $data['name']));
        }
    }

    private function fetch($data)
    {
        $this->data = array_merge([
            'id' => '',
            'name' => '',
            'description' => '',
            'requiredVersion' => '',
            'author' => '',
            'authorUrl' => '',
            'pluginFile' => '',
            'templateDir' => '',
        ], $data);

        $this->dir = dirname($this->data['pluginFile']);
        $this->url = plugins_url('', $this->data['pluginFile']);
        $this->basename = plugin_basename($this->data['pluginFile']);

        $this->id = $this->data['id'];
        $this->name = $this->data['name'];
        $this->description = $this->data['description'];
        $this->requiredVersion = $this->data['requiredVersion'];
        $this->version = $this->data['version'];
        $this->author = $this->data['author'];
        $this->authorUrl = $this->data['authorUrl'];
        $this->templateDir = $this->data['templateDir'];
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