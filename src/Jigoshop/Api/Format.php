<?php

namespace Jigoshop\Api;

/**
 * Class Format
 * @package Jigoshop\Api;
 * @author Krzysztof Kasowski
 */
class Format
{
    private $format;

    public function __construct($format)
    {
        $this->format = $format;
    }

    public function get($data)
    {
        if($this->format == 'xml') {
            $parser = new Format\Xml();
        } else {
            $parser = new Format\Json();
        }

        $parser->setHeaders();

        return $parser->parse($data);
    }
}