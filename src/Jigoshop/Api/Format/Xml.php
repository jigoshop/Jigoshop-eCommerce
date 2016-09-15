<?php

namespace Jigoshop\Api\Format;

/**
 * Class Xml
 * @package Jigoshop\Api\Format;
 * @author Krzysztof Kasowski
 */
class Xml implements FormatInterface
{
    public function setHeaders()
    {
        header('Content-type: text/xml');
    }
    public function parse(array $response)
    {
        return $this->arrayToXML($response);
    }

    private function arrayToXML($array){
        return \Spatie\ArrayToXml\ArrayToXml::convert($array);
    }
}