<?php

/*
 * This file is part of Slim HTTP Basic Authentication middleware
 *
 * Copyright (c) 2013-2017 Mika Tuupola
 *
 * Licensed under the MIT license:
 *   http://www.opensource.org/licenses/mit-license.php
 *
 * Project home:
 *   https://github.com/tuupola/slim-basic-auth
 *
 */

namespace Slim\Middleware\HttpBasicAuthentication;

use Zend\Diactoros\Request;
use Zend\Diactoros\ServerRequestFactory;
use Zend\Diactoros\Response;
use Zend\Diactoros\Uri;

class RequestPathTest extends \PHPUnit_Framework_TestCase
{

    public function testShouldAcceptArrayAndStringAsPath()
    {
        $request = (new Request())
            ->withUri(new Uri("https://example.com/admin/protected"))
            ->withMethod("GET");

        $rule = new RequestPathRule(["path" => "/admin"]);
        $this->assertTrue($rule($request));

        $rule = new RequestPathRule(["path" => ["/admin"]]);
        $this->assertTrue($rule($request));
    }

    public function testShouldAuthenticateEverything()
    {
        $request = (new Request())
            ->withUri(new Uri("https://example.com/"))
            ->withMethod("GET");

        $rule = new RequestPathRule(["path" => "/"]);
        $this->assertTrue($rule($request));

        $request = (new Request())
            ->withUri(new Uri("https://example.com/api"))
            ->withMethod("GET");

        $this->assertTrue($rule($request));
    }

    public function testShouldAuthenticateOnlyApi()
    {
        $request = (new Request())
            ->withUri(new Uri("https://example.com/"))
            ->withMethod("GET");

        $rule = new RequestPathRule(["path" => "/api"]);
        $this->assertFalse($rule($request));

        $request = (new Request())
            ->withUri(new Uri("https://example.com/api"))
            ->withMethod("GET");

        $this->assertTrue($rule($request));
    }

    public function testShouldAuthenticateCreateAndList()
    {
        /* Authenticate only create and list actions */
        $rule = new RequestPathRule([
            "path" => ["/api/create", "/api/list"]
        ]);

        /* Should not authenticate */
        $request = (new Request())
            ->withUri(new Uri("https://example.com/api"))
            ->withMethod("GET");
        $this->assertFalse($rule($request));

        /* Should authenticate */
        $request = (new Request())
            ->withUri(new Uri("https://example.com/api/create"))
            ->withMethod("GET");
        $this->assertTrue($rule($request));

        /* Should authenticate */
        $request = (new Request())
            ->withUri(new Uri("https://example.com/api/list"))
            ->withMethod("GET");
        $this->assertTrue($rule($request));

        /* Should not authenticate */
        $request = (new Request())
            ->withUri(new Uri("https://example.com/api/ping"))
            ->withMethod("GET");
        $this->assertFalse($rule($request));
    }

    public function testShouldPassthroughLogin()
    {
        $request = (new Request())
            ->withUri(new Uri("https://example.com/api"))
            ->withMethod("GET");

        $rule = new RequestPathRule([
            "path" => "/api",
            "passthrough" => ["/api/login"]
        ]);
        $this->assertTrue($rule($request));

        $request = (new Request())
            ->withUri(new Uri("https://example.com/api/login"))
            ->withMethod("GET");

        $this->assertFalse($rule($request));
    }

    public function testBug50ShouldAuthenticateMultipleSlashes()
    {
        $request = (new Request)
            ->withUri(new Uri("https://example.com/"))
            ->withMethod("GET");

        $rule = new RequestPathRule(["path" => "/v1/api"]);
        $this->assertFalse($rule($request));

        $request = (new Request)
            ->withUri(new Uri("https://example.com/v1/api"))
            ->withMethod("GET");

        $this->assertTrue($rule($request));

        $request = (new Request)
            ->withUri(new Uri("https://example.com/v1//api"))
            ->withMethod("GET");

        $this->assertTrue($rule($request));

        $request = (new Request)
            ->withUri(new Uri("https://example.com/v1//////api"))
            ->withMethod("GET");

        $this->assertTrue($rule($request));

        $request = (new Request)
            ->withUri(new Uri("https://example.com//v1/api"))
            ->withMethod("GET");

        $this->assertTrue($rule($request));

        $request = (new Request)
            ->withUri(new Uri("https://example.com//////v1/api"))
            ->withMethod("GET");

        $this->assertTrue($rule($request));
    }
}
