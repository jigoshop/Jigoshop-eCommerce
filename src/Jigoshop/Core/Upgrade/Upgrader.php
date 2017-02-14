<?php

namespace Jigoshop\Core\Upgrade;

use Jigoshop\Container;
use WPAL\Wordpress;

/**
 * @package Jigoshop\Core\Upgrade;
 * @author Krzysztof Kasowski
 */
interface Upgrader
{
    /**
     * @param Wordpress $wp
     * @param Container $di
     */
    public function up(Wordpress $wp, Container $di);

    /**
     * @param Wordpress $wp
     * @param Container $di
     */
    public function down(Wordpress $wp, Container $di);
}