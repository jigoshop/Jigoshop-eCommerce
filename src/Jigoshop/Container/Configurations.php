<?php

namespace Jigoshop\Container;

use Jigoshop\Container;
use Jigoshop\Container\Configuration\ConfigurationInterface;

/**
 * Class Configurations
 * @package Jigoshop\Container
 * @author Krzysztof Kasowski
 */
class Configurations
{
    /** @var  Configuration[] */
    private $configurations = array();

    /**
     * @param Container $container
     */
    public function init(Container $container)
    {
        foreach ($this->configurations as $configuration) {
            $configuration->initClassLoader($container->classLoader);
            $configuration->initServices($container->services);
            $configuration->initTags($container->tags);
            $configuration->initTriggers($container->triggers);
            $configuration->initFactories($container->factories);
        }
    }

    /**
     * @param ConfigurationInterface $configuration
     */
    public function addConfigurations(ConfigurationInterface $configuration)
    {
        $this->configurations[] = $configuration;
    }

    /**
     * @return Configuration[]
     */
    public function getConfiguration()
    {
        return $this->configurations;
    }
}
