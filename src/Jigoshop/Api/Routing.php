<?php

namespace Jigoshop\Api;

use Jigoshop\Api\Routing\NotFound;
use Jigoshop\Exception;

/**
 * Class Routing
 * @package Jigoshop\Api;
 * @author Krzysztof Kasowski
 */
class Routing
{
    const RESERVED_INDEX = '__action';
    private $routes = array();

    public function add($uri, $action)
    {
        $uri = trim($uri, '/');
        $dividedUri = array_reverse(array_filter(explode('/', $uri)));

        $parsedRoute = array(
            self::RESERVED_INDEX => $action
        );
        for($i = 0; $i < count($dividedUri); $i++) {
            if($dividedUri[$i] == self::RESERVED_INDEX) {
                // TODO throw
                return;
            }
            $parsedRoute = array($dividedUri[$i] => $parsedRoute);
        }

        $this->routes = array_replace_recursive($parsedRoute, $this->routes);
    }

    public function getAll()
    {
        return $this->routes;
    }

    public function match($uri)
    {
        $uri = trim($uri, '/');
        $dividedUri = array_filter(explode('/', $uri));
        $routes = $this->getAll();
        $params = array();
        for($i = 0; $i < count($dividedUri); $i++) {
            if(isset($routes[$dividedUri[$i]])) {
                $routes = $routes[$dividedUri[$i]];
            } else {
                $matchedPatterns = $this->getMatchedPatterns($dividedUri[$i]);
                $keys = array_keys($routes);
                $validKeys = array_values(array_filter($matchedPatterns, function($item) use ($keys){
                    return in_array($item, $keys);
                }));
                if(empty($validKeys)) {
                    throw new Exception(sprintf(__('Routing not found: %s', 'jigoshop'), $uri));
                } else {
                    $params[] = $dividedUri[$i];
                    $routes = $routes[$validKeys[0]];
                }
            }
        }

        if(!isset($routes[self::RESERVED_INDEX])) {
            throw new Exception(sprintf(__('Routing not found: %s', 'jigoshop'), $uri));
        }

        return array(
            'params' => $params,
            'action' => $routes[self::RESERVED_INDEX],
        );
    }

    private function getMatchedPatterns($uriPart)
    {
        $patterns = array();
        if(preg_match('/^\d+$/', $uriPart)) {
            $patterns[] = '{int:'.strlen($uriPart).'}';
            $patterns[] = '{int}';
        }
        if(preg_match('/^[0-9.]+$/', $uriPart)) {
            $patterns[] = '{float:'.strlen($uriPart).'}';
            $patterns[] = '{float}';
        }
        if(preg_match('/^[a-zA-Z]+$/', $uriPart)) {
            $patterns[] = '{string:'.strlen($uriPart).'}';
            $patterns[] = '{string}';
        }
        if(preg_match('/^(.+)$/', $uriPart)) {
            $patterns[] = '{any:'.strlen($uriPart).'}';
            $patterns[] = '{any}';
        }

        return $patterns;
    }
}