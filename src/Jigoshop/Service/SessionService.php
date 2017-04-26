<?php

namespace Jigoshop\Service;

use Jigoshop\Core\Options;
use Jigoshop\Entity\Session;
use Jigoshop\Factory\Session as Factory;
use WPAL\Wordpress;

/**
 * Class Session
 * @package Jigoshop\Service;
 * @author Krzysztof Kasowski
 */
abstract class SessionService implements SessionServiceInterface
{
    /** @var  Wordpress */
    private $wp;
    /** @var  Options  */
    private $options;
    /** @var Factory  */
    private $factory;
    /** @var  Session[] */
    private $sessionsToSave;

    /**
     * SessionService constructor.
     * @param Wordpress $wp
     * @param Factory $factory
     */
    public function __construct(Wordpress $wp, Options $options, Factory $factory)
    {
        $this->wp = $wp;
        $this->options = $options;
        $this->factory = $factory;
        $this->sessionsToSave = [];

        $this->wp->addAction('shutdown', [$this, 'saveAllSessions']);
    }

    /**
     * @return Wordpress
     */
    public function getWp()
    {
        return $this->wp;
    }

    /**
     * @return Options
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @return Factory
     */
    public function getFactory()
    {
        return $this->factory;
    }

    /**
     * @param string $key
     *
     * @return Session
     */
    abstract public function get($key);

    /**
     * @return string
     */
    public function getCurrentKey()
    {
        if(isset($_COOKIE['jigoshop_session_key'])) {
            $key = $_COOKIE['jigoshop_session_key'];
        } else {
            $key = $this->generateSessionKey();
        }

        return $key;
    }

    private function generateSessionKey()
    {
        $key = 'jigoshop_'.md5(microtime().rand());
        setcookie('jigoshop_session_key', $key, time() + (86400 * 30), "/");

        return $key;
    }

    /**
     * @param Session $session
     */
    abstract public function save(Session $session);

    /**
     * @param Session $session
     */
    public function addSessionToSave(Session $session)
    {
        $this->sessionsToSave[$session->getKey()] = $session;
    }
    /**
     * Save all sessions
     */
    public function saveAllSessions()
    {
        foreach($this->sessionsToSave as $session) {
            $this->save($session);
        }
    }
}