<?php

use Codedor\FilamentRedirects\Http\Middleware\Redirects;
use Codedor\FilamentRedirects\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;

uses(TestCase::class)->in('Feature');
uses(RefreshDatabase::class)->in('Feature');

function createResponse(string $uri, string $method = 'GET')
{
    $request = Request::create($uri, $method);

    $middleware = new Redirects;

    return $middleware->handle($request, function () {});
}
