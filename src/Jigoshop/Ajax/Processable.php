<?php

namespace Jigoshop\Ajax;

/**
 * @package Jigoshop\Ajax;
 * @author Krzysztof Kasowski
 */
interface Processable
{
    /**
     * @return mixed
     */
    public function process();
}