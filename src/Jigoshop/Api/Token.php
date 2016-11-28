<?php

namespace Jigoshop\Api;

/**
 * Class Token
 * @package Jigoshop\Api;
 * @author Krzysztof Kasowski
 */
class Token
{
    public $decoded;

    public function hydrate($decoded)
    {
        $this->decoded = $decoded;
    }

    public function hasScope(array $scope)
    {
        
    }
}