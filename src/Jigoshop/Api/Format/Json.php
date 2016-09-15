<?php

namespace Jigoshop\Api\Format;

/**
 * Class Json
 * @package Jigoshop\Api\Format;
 * @author Krzysztof Kasowski
 */
class Json implements FormatInterface
{
    public function setHeaders()
    {
        header('Content-type: application/json');
    }

    public function parse(array $response)
    {
        return json_encode($response);
    }
}