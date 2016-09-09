<?php

namespace Jigoshop\Api\Format;

/**
 * Class FormatInterface
 * @package Jigoshop\Api\Format;
 * @author Krzysztof Kasowski
 */
interface FormatInterface
{
    public function setHeaders();
    public function parse(array $response);
}