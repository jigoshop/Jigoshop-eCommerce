<?php

namespace Jigoshop\Entity\Product;

/**
 * Class Attachment
 * @package Jigoshop\Entity\Product;
 * @author Krzysztof Kasowski
 */
abstract class Attachment implements \JsonSerializable
{
    /** @var  int  */
    private $id;
    /** @var  string  */
    private $title;
    /** @var  string  */
    private $url;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * @return string
     */
    abstract public function getType();

    /**
     * @return array
     */
    public function getStateToSave()
    {
        return [
            'id' => $this->id,
            'type' => $this->getType(),
            'title' => $this->getTitle(),
            'url' => $this->url,
        ];
    }

    /**
     * @param array $state
     */
    public function restoreState(array $state)
    {
        $this->id = isset($state['id']) ? $state['id'] : null;
        $this->title = isset($state['title']) ? $state['title'] : null;
        $this->url = isset($state['url']) ? $state['url'] : null;
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return [
            'id' => $this->id,
            'type' => $this->getType(),
            'title' => $this->getTitle(),
            'url' => $this->url,
        ];
    }
}