<?php

namespace Jigoshop\Api\Response\V1;

use Jigoshop\Api\Response\ResponseInterface;
use Jigoshop\Container;

/**
 * Class Test
 * @package Jigoshop\Api\V1;
 * @author Krzysztof Kasowski
 */
class Test implements ResponseInterface
{

    public function init(Container $di)
    {

    }

    public function getTest()
    {
        return array('time' => microtime(1) - WP_START_TIME);
    }
}