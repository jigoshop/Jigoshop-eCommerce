<?php

namespace Jigoshop\Service\Session;

use Jigoshop\Entity\Session;
use Jigoshop\Service\SessionService;

/**
 * Class Transient
 * @package Jigoshop\Service\Session;
 * @author Krzysztof Kasowski
 */
class Transient extends SessionService
{
    /** @var Session[] */
    private $sessions = array();

    /**
     * @param string $key
     *
     * @return Session
     */
    public function get($key)
    {
        if (!isset($this->sessions[$key])) {
            $data = array('key' => $key);
            if (isset($_SESSION[$key])) {
                $data['fields'] = get_transient('jigoshop_session_'.$key);
            }

            $this->sessions[$key] = $this->getFactory()->fetch($data);
            $this->sessions[$key]->setSessionService($this);
        }

        return $this->sessions[$key];
    }

    /**
     * @param Session $session
     */
    public function save(Session $session)
    {
        set_transient('jigoshop_session_'.$session->getKey(), $session->getFields(), 2592000);//30 DAYS
    }
}