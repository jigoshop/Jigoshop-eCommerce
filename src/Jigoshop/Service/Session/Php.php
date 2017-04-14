<?php
namespace Jigoshop\Service\Session;

use Jigoshop\Entity\Session;
use Jigoshop\Service\SessionService;

/**
 * Class Php
 * @package Jigoshop\Service\Session
 * @author Krzysztof Kasowski
 */
class Php extends SessionService
{
    /** @var Session[] */
    private $sessions = [];

    /**
     * @param string $key
     *
     * @return Session
     */
    public function get($key)
    {
        if (!isset($this->sessions[$key])) {
            $data = ['key' => $key];
            if (isset($_SESSION[$key])) {
                $data['fields'] = $_SESSION[$key];
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
        $_SESSION[$session->getKey()] = $session->getFields();
    }
}