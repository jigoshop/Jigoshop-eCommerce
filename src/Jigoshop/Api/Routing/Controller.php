<?php

namespace Jigoshop\Api\Routing;

use Jigoshop\Api\Routing;

/**
 * Class Controller
 * @package Jigoshop\Api\Routing;
 * @author Krzysztof Kasowski
 */
class Controller implements ControllerInterface
{
    /**
     * @param Routing $routing
     * @param $version
     */
    public function onGet(Routing $routing, $version)
    {
        if($version == 1) {
            $routing->add('/products', 'Jigoshop\Api\Response\V1\Products@getList');
            $routing->add('/products/{int}', 'Jigoshop\Api\Response\V1\Products@getSingle');
        }
    }

    /**
     * @param Routing $routing
     * @param $version
     */
    public function onPost(Routing $routing, $version)
    {
        // TODO: Implement onPost() method.
    }

    /**
     * @param Routing $routing
     * @param $version
     */
    public function onPut(Routing $routing, $version)
    {
        // TODO: Implement onPut() method.
    }

    /**
     * @param Routing $routing
     * @param $version
     */
    public function onDelete(Routing $routing, $version)
    {
        // TODO: Implement onDelete() method.
    }
}