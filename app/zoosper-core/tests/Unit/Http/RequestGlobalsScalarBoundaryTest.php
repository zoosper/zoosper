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

it('normalises malformed form and uploaded-file maps at the globals boundary', function (): void {
    $server = $_SERVER;
    $post = $_POST;
    $files = $_FILES;

    try {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['REQUEST_URI'] = '/admin/media/upload';
        $_POST = ['title' => '0', 10 => 'discarded', 'nested' => ['one', ['two']]];
        $_FILES = ['media_file' => ['name' => 'safe.jpg'], 20 => ['name' => 'discarded'], 'invalid' => 'discarded'];

        $request = Request::fromGlobals();

        expect($request->form())->toBe(['title' => '0', 'nested' => ['one', 'two']])
            ->and($request->uploadedFile('media_file'))->toBe(['name' => 'safe.jpg'])
            ->and($request->uploadedFile('invalid'))->toBe([]);
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
