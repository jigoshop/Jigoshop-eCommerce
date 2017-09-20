<?php

namespace Jigoshop\Admin;

/**
 * @package Jigoshop\Admin;
 * @author Krzysztof Kasowski
 */
interface DashboardInterface
{
    /** @return string Title of page. */
    public function getTitle();

    /** @return string Required capability to view the page. */
    public function getCapability();

    /** @return string Menu slug. */
    public function getMenuSlug();

    /** Displays the page. */
    public function display();
}