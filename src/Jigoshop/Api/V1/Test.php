<?php

namespace Jigoshop\Api\V1;

use Jigoshop\Api\Processable;
use Jigoshop\Container;

/**
 * Class Test
 * @package Jigoshop\Api\V1;
 * @author Krzysztof Kasowski
 */
class Test implements Processable
{
    public function init(Container $di)
    {

    }

    public function processResponse()
    {
        return array('time' => microtime(1) - WP_START_TIME);
    }
}