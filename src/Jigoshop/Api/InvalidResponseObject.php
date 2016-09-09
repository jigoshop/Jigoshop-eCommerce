<?php

namespace Jigoshop\Api;

use Jigoshop\Exception;

/**
 * Class InvalidResponseObject
 * @package Jigoshop\Api;
 * @author Krzysztof Kasowski
 */
class InvalidResponseObject extends Exception
{
    /** @var  string  */
    private $methodName;

    public function __construct($message = '', $code = 0, $previous = null, $methodName = '')
    {
        parent::__construct($message, $code, $previous);
        $this->methodName = $methodName;
    }

    /**
     * @return string
     */
    public function getMethodName()
    {
        return $this->methodName;
    }
}