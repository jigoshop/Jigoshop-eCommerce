<?php

namespace Jigoshop\Container;

/**
 * Class Triggers
 *
 * @method void add(string $key, string $instance, string $method, string[] $params)
 *
 * @package Jigoshop\Container
 *
 * @author  Krzysztof Kasowski
 */
class Triggers
{
    /** @var array */
    private $triggers;

    public function __call($method, $args)
    {
        if ($method == 'add') {
            if (count($args) < 2) {
                return false;
            } elseif (count($args) == 2) {
                $this->_add3($args[0], $args[1]);
            } elseif (count($args) == 3) {
                $this->_add2($args[0], $args[1], $args[2]);
            } else {
                $this->_add1($args[0], $args[1], $args[2], $args[3]);
            }
        }
    }

    /**
     * @param string $key
     * @param string $instance
     * @param string $method
     * @param string[] $params
     */
    private function _add1($key, $instance, $method, $params)
    {
        $this->triggers[$key][] = ['instance' => $instance, 'method' => $method, 'params' => $params];
    }

    /**
     * @param string $key
     * @param string $method
     * @param string[] $params
     */
    private function _add2($key, $method, $params)
    {
        $this->triggers[$key][] = ['instance' => $key, 'method' => $method, 'params' => $params];
    }

    /**
     * @param string $key
     * @param string[] $params
     */
    private function _add3($key, $params)
    {
        $this->triggers[$key][] = ['instance' => '', 'method' => '', 'params' => $params];
    }

    /**
     * @param string $key
     *
     * @return array
     */
    public function get($key)
    {
        return $this->triggers[$key];
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    public function exists($key)
    {
        return isset($this->triggers[$key]);
    }

    /**
     * @param        $object
     * @param string $methodName
     * @param array $params
     *
     * @return mixed
     */
    public function callMethod($object, $methodName, $params)
    {
        if ($object && $methodName) {
            return call_user_func_array([$object, $methodName], $params);
        }

        return false;
    }
}