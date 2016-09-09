<?php

namespace Jigoshop\Api\Response;

use Jigoshop\Container;

/**
 * @package Jigoshop\Api\Response;
 * @author Krzysztof Kasowski
 */
interface ResponseInterface
{
    /**
     * @param Container $di
     */
    public function init(Container $di);
}