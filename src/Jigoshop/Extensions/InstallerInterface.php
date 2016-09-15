<?php

namespace Jigoshop\Extensions;

use Jigoshop\Container;

/**
 * @package Jigoshop\Core;
 * @author Krzysztof Kasowski
 */
interface InstallerInterface
{
    /**
     * @param Container $di
     */
    public function init(Container $di);

    /**
     * @return null
     */
    public function install();
}