<?php

namespace Jigoshop\Middleware;

use Jigoshop\Api\Validation\Validator;
use Jigoshop\Exception;
use Slim\App;

class RequiredFieldsMiddleware
{
    /** namespace to api controllers */
    const API_NAMESPACE = 'Jigoshop\Api\Routes\V1';

    /** @var App */
    private $app;
    /** @var array */
    private $options;

    /**
     * RequiredFieldsMiddleware constructor.
     * @param App $app
     */
    public function __construct(App $app, array $options = [])
    {
        $this->app = $app;
        $this->options = $options;
    }

    /**
     * Middleware invokable class
     *
     * @param  \Psr\Http\Message\ServerRequestInterface $request PSR7 request
     * @param  \Psr\Http\Message\ResponseInterface $response PSR7 response
     * @param  callable $next Next middleware
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function __invoke($request, $response, $next)
    {
        if (in_array($method = $request->getMethod(), ['POST', 'PUT'])) {
            $requirementsName = isset($this->options['requirementsName']) ? $this->options['requirementsName'] :
                (explode('/', $request->getUri()->getPath())[1] ?: null);
            $required = Validator::getInstance()->getRequiredFieldsArrayForMethod($requirementsName, $method);
            $this->validateRequiredFields($request->getParsedBody(), $required);
        }
        return $next($request, $response);
    }

    /**
     * @param $data
     * @param $requiredFields
     * @return bool
     */
    private function validateRequiredFields($data, $requiredFields)
    {
//        if (count($data) < count($requiredFields)) {
//            throw new Exception('Some of required params are missing');
//        }
        $missing = [];
        $error = null;
        foreach ($requiredFields as $key) {
            if (array_key_exists($key, $data)) {
                if (isset($data[$key])) {
                } else {
                    $error = implode(",", $requiredFields);
                }
            } else {
                array_push($missing, $key);
            }
        }
        if (!empty($missing) || $error) {
            $message = 'Some of required params are missing: ' . implode($missing, ', ');
            throw new Exception($message, 422);
        }
        return true;
    }
}