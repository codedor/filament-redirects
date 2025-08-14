<?php

use Codedor\FilamentRedirects\Models\Redirect;
use Symfony\Component\HttpKernel\Exception\HttpException;

it('will skip if it is a post request', function () {
    $response = createResponse('/from', 'POST');

    $this->assertEquals($response, null);
});

it('will not skip if it is a get request', function () {
    Redirect::factory()->create([
        'sort_order' => 1,
        'from' => '/from',
        'to' => '/to',
        'status' => 302,
        'pass_query_string' => false,
        'online' => true,
    ]);

    $response = createResponse('/from');

    $this->assertEquals($response->getStatusCode(), 302);
    $this->assertEquals($response->getTargetUrl(), 'http://localhost/to');
});

it('will only redirect for an online url map', function () {
    Redirect::factory()->create([
        'sort_order' => 1,
        'from' => '/from',
        'to' => '/to',
        'status' => 302,
        'pass_query_string' => false,
        'online' => false,
    ]);

    $response = createResponse('/from');

    $this->assertEquals($response, null);
});

it('will keep sort order in account', function () {
    Redirect::factory()->createQuietly([
        'sort_order' => 2,
        'from' => '/from',
        'to' => '/to',
        'status' => 302,
        'pass_query_string' => false,
        'online' => true,
    ]);
    Redirect::factory()->createQuietly([
        'sort_order' => 1,
        'from' => '/from',
        'to' => '/to-2',
        'status' => 301,
        'pass_query_string' => false,
        'online' => true,
    ]);

    $response = createResponse('/from');

    $this->assertEquals($response->getStatusCode(), 301);
    $this->assertEquals($response->getTargetUrl(), 'http://localhost/to-2');
});

it('will not redirect if no url map matches', function () {
    Redirect::factory()->create([
        'sort_order' => 1,
        'from' => '/from',
        'to' => '/to',
        'status' => 302,
        'pass_query_string' => false,
        'online' => true,
    ]);

    $response = createResponse('/to');

    $this->assertEquals($response, null);
});

it('will redirect with query string', function () {
    Redirect::factory()->create([
        'sort_order' => 1,
        'from' => '/from?query=string',
        'to' => '/to',
        'status' => 302,
        'pass_query_string' => true,
        'online' => true,
    ]);

    $response = createResponse('/from?query=string');

    $this->assertEquals($response->getStatusCode(), 302);
    $this->assertEquals($response->getTargetUrl(), 'http://localhost/to?query=string');
});

it('will abort if status is 410', function () {
    Redirect::factory()->create([
        'sort_order' => 1,
        'from' => '/from',
        'to' => null,
        'status' => 410,
        'pass_query_string' => true,
        'online' => true,
    ]);

    $this->expectException(HttpException::class);

    createResponse('/from');
});

it('will redirect with a wildcard', function () {
    Redirect::factory()->create([
        'sort_order' => 1,
        'from' => '/from/*',
        'to' => '/to',
        'status' => 302,
        'pass_query_string' => false,
        'online' => true,
    ]);

    $response = createResponse('/from');

    $this->assertEquals($response, null);

    $response2 = createResponse('/from/wildcard');

    $this->assertEquals($response2->getStatusCode(), 302);
    $this->assertEquals($response2->getTargetUrl(), 'http://localhost/to');
});

it('will redirect if redirect has a trailing slash', function () {
    Redirect::factory()->create([
        'sort_order' => 1,
        'from' => '/from/',
        'to' => '/to',
        'status' => 302,
        'pass_query_string' => true,
        'online' => true,
    ]);

    $response = createResponse('/from');

    $this->assertEquals($response->getStatusCode(), 302);
    $this->assertEquals($response->getTargetUrl(), 'http://localhost/to');
});

it('will redirect if redirect has no trailing slash', function () {
    Redirect::factory()->create([
        'sort_order' => 1,
        'from' => '/from/',
        'to' => '/to',
        'status' => 302,
        'pass_query_string' => true,
        'online' => true,
    ]);

    $response = createResponse('/from/');

    $this->assertEquals($response->getStatusCode(), 302);
    $this->assertEquals($response->getTargetUrl(), 'http://localhost/to');
});
