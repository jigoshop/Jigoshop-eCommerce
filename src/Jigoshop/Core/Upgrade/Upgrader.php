<?php

namespace Jigoshop\Core\Upgrade;

use WPAL\Wordpress;

/**
 * @package Jigoshop\Core\Upgrade;
 * @author Krzysztof Kasowski
 */
interface Upgrader
{
    /**
     * @param Wordpress $wp
     */
    public function up(Wordpress $wp);

    /**
     * @param Wordpress $wp
     */
    public function down(Wordpress $wp);
}