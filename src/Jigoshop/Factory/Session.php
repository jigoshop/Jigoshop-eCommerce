<?php

namespace Jigoshop\Factory;

use Jigoshop\Core\Options;
use WPAL\Wordpress;

/**
 * Class Session
 * @package Jigoshop\Factory;
 * @author Krzysztof Kasowski
 */
class Session implements EntityFactoryInterface
{
    /** @var  Wordpress */
    private $wp;
    /** @var  Options */
    private $options;

    /**
     * Session constructor.
     * @param Wordpress $wp
     * @param Options $options
     */
    public function __construct(Wordpress $wp, Options $options)
    {
        $this->wp = $wp;
        $this->options = $options;
    }
    /**
     * @param $id int
     *
     * @return \Jigoshop\Entity\Session
     */
    public function create($id)
    {
        //Silence
    }

    /**
     * @param $data array
     *
     * @return \Jigoshop\Entity\Session
     */
    public function fetch($data)
    {
        $session = new \Jigoshop\Entity\Session();
        $session->restoreState($data);

        return $session;
    }
}