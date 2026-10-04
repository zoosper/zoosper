<?php

declare(strict_types=1);

use Zoosper\Core\Http\Request;

it('normalises malformed non-string server values at the request boundary', function (): void {
    $server = $_SERVER;
    $post = $_POST;
    $files = $_FILES;

    try {
        $_SERVER['REQUEST_URI'] = ['unexpected'];
        $_SERVER['REQUEST_METHOD'] = ['unexpected'];
        $_SERVER['HTTP_HOST'] = ['unexpected'];
        $_POST = [];
        $_FILES = [];

        $request = Request::fromGlobals();

        expect($request->method())->toBe('GET')
            ->and($request->path())->toBe('/')
            ->and($request->host())->toBe('localhost')
            ->and($request->queryString())->toBe('')
            ->and($request->queryParams())->toBe([]);
    } finally {
        $_SERVER = $server;
        $_POST = $post;
        $_FILES = $files;
    }
});

it('preserves zero-like query values without truthy fallback', function (): void {
    $server = $_SERVER;
    $post = $_POST;
    $files = $_FILES;

    try {
        $_SERVER['REQUEST_URI'] = '/catalog?page=0&q=0';
        $_SERVER['REQUEST_METHOD'] = 'get';
        $_SERVER['HTTP_HOST'] = 'Example.Test:8080';
        $_POST = [];
        $_FILES = [];

        $request = Request::fromGlobals();

        expect($request->method())->toBe('GET')
            ->and($request->path())->toBe('/catalog')
            ->and($request->host())->toBe('example.test')
            ->and($request->queryString())->toBe('page=0&q=0')
            ->and($request->query('page'))->toBe('0')
            ->and($request->query('q'))->toBe('0');
    } finally {
        $_SERVER = $server;
        $_POST = $post;
        $_FILES = $files;
    }
});
