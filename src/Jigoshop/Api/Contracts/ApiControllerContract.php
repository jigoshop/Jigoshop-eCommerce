<?php

namespace Jigoshop\Api\Contracts;

use Slim\Http\Request;
use Slim\Http\Response;

interface ApiControllerContract{
    public function findAll(Request $request, Response $response, $args);
    public function findOne(Request $request, Response $response, $args);
    public function create(Request $request, Response $response, $args);
    public function update(Request $request, Response $response, $args);
    public function delete(Request $request, Response $response, $args);

}