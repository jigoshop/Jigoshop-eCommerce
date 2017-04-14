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
     * @param $requirementsName
     * @return array|null
     */
    public function getRequiredFieldsArray($requirementsName)
    {
        return isset($this->requiredFieldsArray[$requirementsName]) ? $this->requiredFieldsArray[$requirementsName] : null;
    }

    /**
     * @param $requirementsName
     * @param $method
     * @return array|null
     */
    public function getRequiredFieldsArrayForMethod($requirementsName, $method)
    {
        $method = strtolower($method);
        $forClass = $this->getRequiredFieldsArray($requirementsName);
        return isset($forClass[$method]) ? $forClass[$method] : $forClass;
    }


}