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
    private $configurations = [];

    /**
     * @param Container\Configurations\ConfigurationInterface $configuration
     */
    public function add(Container\Configurations\ConfigurationInterface $configuration)
    {
        $this->configurations[] = $configuration;
    }

    /**
     * @return Container\Configurations\ConfigurationInterface[]
     */
    public function getAll()
    {
        return $this->configurations;
    }
}
