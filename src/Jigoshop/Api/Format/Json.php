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
        $response = $this->fixArray($response);
        return json_encode($response);
    }

    private function fixArray($array)
    {
        $return = array();
        foreach ($array as $key => $value) {
            if(is_array($value) && $this->isAssoc($value)) {
                $return[$key] = $this->fixArray($value);
            } elseif (is_array($value)) {
                return $value;
            } else {
                $return[$key] = $value;
            }
        }

        return $return;
    }

    private function isAssoc($array)
    {
        return array_keys($array) !== range(0, count($array) - 1);
    }
}