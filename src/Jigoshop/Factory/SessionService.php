<?php

namespace Jigoshop\Factory;

use Jigoshop\Core\Options;
use Jigoshop\Factory\Session as Factory;
use Jigoshop\Service;
use WPAL\Wordpress;

/**
 * Class SessionService
 * @package Jigoshop\Factory;
 * @author Krzysztof Kasowski
 */
class SessionService
{
    /** @var Wordpress */
    private $wp;
    /** @var Options */
    private $options;
    /** @var Factory */
    private $factory;

    /**
     * OrderService constructor.
     * @param Wordpress $wp
     * @param Options $options
     * @param Factory $factory
     */
    public function __construct(Wordpress $wp, Options $options, Factory $factory)
    {
        $this->wp = $wp;
        $this->options = $options;
        $this->factory = $factory;
    }

    /**
     * @return SessionService
     */
    public function getService()
    {
        switch ($this->options->get('advanced.session', 'php')) {
            case 'php':
                $service = new Service\Session\Php($this->wp, $this->options, $this->factory);
                break;
            case 'transient':
                $service = new Service\Session\Transient($this->wp, $this->options, $this->factory);
                break;
            default:
                $service = $this->wp->applyFilters('jigoshop\core\get_session_service', '', $this->wp, $this->options, $this->factory);
                break;
        }

        return $service;
    }
}