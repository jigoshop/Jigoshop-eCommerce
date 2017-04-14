<?php

namespace Jigoshop\Entity;

use Jigoshop\Service\SessionService;

/**
 * Class Session
 * @package Jigoshop\Entity;
 * @author Krzysztof Kasowski
 */
class Session implements EntityInterface
{
    /** @var  int */
    private $id;
    /** @var  string */
    private $key;
    /** @var  array */
    private $fields;
    /** @var  bool */
    private $modified;
    /** @var  SessionService */
    private $sessionService;

    /**
     * This is not used anymore
     * @return string Entity ID.
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param string $key
     */
    public function setKey($key)
    {
        $this->key = $key;
        $this->setAsDirty();
    }

    /**
     * @return array
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * @param array $fields
     */
    public function setFields(array $fields)
    {
        $this->fields = $fields;
        $this->setAsDirty();
    }

    /**
     * @param SessionServiceInterface $sessionService
     */
    public function setSessionService($sessionService)
    {
        $this->sessionService = $sessionService;
    }

    /**
     * @param $field
     *
     * @return mixed
     */
    public function getField($field)
    {
        if(isset($this->fields[$field])) {
            return $this->fields[$field];
        }

        return '';
    }

    /**
     * @param string $key
     * @param mixed $value
     */
    public function setField($key, $value)
    {
        $this->fields[$key] = $value;
        $this->setAsDirty();
    }

    /**
     * @param $key
     */
    public function removeField($key)
    {
        unset($this->fields[$key]);
        $this->setAsDirty();
    }

    /**
     * @return array List of fields to update with according values.
     */
    public function getStateToSave()
    {
        return [
            'id' => $this->id,
            'key' => $this->key,
            'fields' => $this->fields,
        ];
    }

    /**
     * @param array $state State to restore entity to.
     */
    public function restoreState(array $state)
    {
        if(isset($state['id'])) {
            $this->id = $state['id'];
        }
        if(isset($state['key'])) {
            $this->key = $state['key'];
        }
        if(isset($state['fields'])) {
            $this->fields = $state['fields'];
        }
    }

    /**
     * @return boolean
     */
    public function isModified()
    {
        return $this->modified;
    }

    public function setAsDirty()
    {
        if($this->modified != true) {
            $this->modified = true;
            $this->sessionService->addSessionToSave($this);
        }
    }
}