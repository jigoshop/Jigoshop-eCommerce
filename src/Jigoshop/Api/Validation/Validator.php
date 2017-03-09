<?php

namespace Jigoshop\Api\Validation;


/**
 *
 * Singleton Class Validator
 * @package Jigoshop\Api\Validation
 */
class Validator
{
    /**
     * @var
     */
    private static $instance;
    /**
     * @var mixed
     */
    protected $requiredFieldsArray;

    /**
     * Validator constructor.
     */
    private function __construct()
    {
        $this->requiredFieldsArray = require 'required_fields.php';
    }

    /**
     * @return Validator
     */
    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }


    /**
     * @param $className
     * @return array|null
     */
    public function getRequiredFieldsArray($className)
    {
        return isset($this->requiredFieldsArray[$className]) ? $this->requiredFieldsArray[$className] : null;
    }

    /**
     * @param $className
     * @param $method
     * @return array|null
     */
    public function getRequiredFieldsArrayForMethod($className, $method)
    {
        $method = strtolower($method);
        $forClass = $this->getRequiredFieldsArray($className);
        return isset($forClass[$method]) ? $forClass[$method] : $forClass;
    }


}