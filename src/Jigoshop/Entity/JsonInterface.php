<?php

namespace Jigoshop\Entity;

use Jigoshop\Container;

/**
 * @package Jigoshop\Entity;
 * @author Krzysztof Kasowski
 */
interface JsonInterface extends \JsonSerializable
{
    /**
     * @param Container $di
     * @param array $json
     */
    public function jsonDeserialize(Container $di, array $json);
}