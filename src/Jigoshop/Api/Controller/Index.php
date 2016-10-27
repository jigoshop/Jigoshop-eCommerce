<?php

namespace Jigoshop\Api\Controller;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Class Index
 * @package Jigoshop\Api;
 * @author Krzysztof Kasowski
 */
class Index
{
    public function __invoke(RequestInterface $request, ResponseInterface $response)
    {
        return $response->withJson(['success' => true, 'message' => 'Hello World!']);
    }
}