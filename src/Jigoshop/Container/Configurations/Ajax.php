<?php

namespace Jigoshop\Container\Configurations;

use Jigoshop\Container\Factories;
use Jigoshop\Container\Services;
use Jigoshop\Container\Tags;
use Jigoshop\Container\Triggers;

/**
 * Class Ajax
 * @package Jigoshop\Container\Configurations;
 * @author Krzysztof Kasowski
 */
class Ajax implements ConfigurationInterface
{

    /**
     * @param Services $services
     *
     * @return mixed
     */
    public function addServices(Services $services)
    {
        $services->setDetails('jigoshop.ajax.get_states', 'Jigoshop\Ajax\GetStates', []);
    }

    /**
     * @param Tags $tags
     *
     * @return mixed
     */
    public function addTags(Tags $tags)
    {
        // TODO: Implement addTags() method.
    }

    /**
     * @param Triggers $triggers
     *
     * @return mixed
     */
    public function addTriggers(Triggers $triggers)
    {
        // TODO: Implement addTriggers() method.
    }

    /**
     * @param Factories $factories
     *
     * @return mixed
     */
    public function addFactories(Factories $factories)
    {
        // TODO: Implement addFactories() method.
    }
}