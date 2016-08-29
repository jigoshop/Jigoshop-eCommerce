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

    private function arrayToXML($array, $xml = false){
        if($xml === false){
            $xml = new \SimpleXMLElement('<document/>');
        }
        foreach($array as $key => $value){
            if(is_array($value)){
                $this->arrayToXML($value, $xml->addChild($key));
            }else{
                $xml->addChild($key, $value);
            }
        }
        return $xml->asXML();
    }
}