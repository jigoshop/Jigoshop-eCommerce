<?php

namespace Jigoshop\Api\Routing;

use Jigoshop\Api\Routing;

/**
 * @package Jigoshop\Api\Routing;
 * @author Krzysztof Kasowski
 */
interface ControllerInterface
{
    /**
     * @param Routing $routing
     * @param $version
     */
    public function onGet(Routing $routing, $version);

    /**
     * @param Routing $routing
     * @param $version
     */
    public function onPost(Routing $routing, $version);

    /**
     * @param Routing $routing
     * @param $version
     */
    public function onPut(Routing $routing, $version);

    /**
     * @param Routing $routing
     * @param $version
     */
    public function onDelete(Routing $routing, $version);
}