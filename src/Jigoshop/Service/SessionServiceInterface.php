<?php

namespace Jigoshop\Service;

use Jigoshop\Entity\Session;

/**
 * @package Jigoshop\Service;
 * @author Krzysztof Kasowski
 */
interface SessionServiceInterface
{
    /**
     * @param string $key
     * @return Session
     */
    public function get($key);

    /**
     * @return string
     */
    public function getCurrentKey();

    /**
     * @param Session $session
     */
    public function save(Session $session);
}