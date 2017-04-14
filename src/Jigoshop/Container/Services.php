<?php

namespace Jigoshop\Container;

use Jigoshop\Exception;
use Jigoshop\Container\Lazy\ProxyLoader;

/**
 * Class Services
 *
 * @package Jigoshop\Container
 * @author  Krzysztof Kasowski
 */
class Services
{
    /** @var array $serviceDetails */
    public $serviceDetails = [];
    /** @var array $services */
    private $services = [];

    /*	public function __construct()
        {
        }*/

    /**
     * @param string $key
     * @param string $name
     * @param array $params
     */
    public function setDetails($key, $name, array $params)
    {
        if ($this->exists($key)) {
            throw new Exception(sprintf('Details info for %s are already set.', $key));
        }

        $serviceDetails = [
            'name' => $name,
            'params' => $params,
            'lazy' => false
        ];

        $this->serviceDetails[$key] = $serviceDetails;
    }

    /**
     * @param string $key
     * @param string $name
     * @param array $params
     * @deprecated Use setDetails instead
     */
    public function setDatails($key, $name, array $params)
    {
        //TODO: remove this after fixing extensions
        $this->setDetails($key, $name, $params);
    }

    /**
     * @param string $key
     * @param bool $value
     */
    public function setLazyStaus($key, $value)
    {
        $this->servicesDatails[$key]['lazy'] = $value;
    }

    /**
     * @param string $key
     * @param mixed $instance
     */
    public function set($key, $instance)
    {
        $this->services[$key] = $instance;
    }

    /**
     * @param string $key
     *
     * @return mixed
     */
    public function get($key)
    {
        if (!isset($this->services[$key])) {
            $this->createInstance($key);
        }

        return $this->services[$key];
    }

    /**
     * @param string $key
     *
     * @return string
     */
    public function getClassName($key)
    {
        return '\\' . $this->serviceDetails[$key]['name'];
    }

    /**
     * @param string $key
     *
     * @return array
     */
    public function getParams($key)
    {
        return $this->serviceDetails[$key]['params'];
    }

    /**
     * @param string $key
     *
     * @return array
     */
    public function getLazyStatus($key)
    {
        return $this->serviceDetails[$key]['lazy'];
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    public function detailsExists($key)
    {
        return isset($this->serviceDetails[$key]);
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    public function exists($key)
    {
        return isset($this->services[$key]);
    }

    /**
     * @param string $key
     * @param array $params
     */
    public function createInstance($key, $params)
    {
        $className = $this->getClassName($key);

        if ($this->getLazyStatus($key)) {
            $instance = new ProxyLoader($className, $params);
        } else {
            $reflection = new \ReflectionClass($className);
            $instance = $reflection->newInstanceArgs($params);
        }

        $this->set($key, $instance);
    }
}
