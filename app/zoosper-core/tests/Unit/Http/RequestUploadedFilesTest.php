<?php

declare(strict_types=1);

use Zoosper\Core\Http\Request;

it('exposes only uploaded files explicitly carried by the request', function (): void {
    $_FILES['media_file'] = ['name' => 'global.jpg'];
    $request = new Request('POST', '/admin/media/upload', files: [
        'media_file' => ['name' => 'request.jpg', 'error' => UPLOAD_ERR_OK],
    ]);

    expect($request->uploadedFile('media_file'))->toBe([
        'name' => 'request.jpg',
        'error' => UPLOAD_ERR_OK,
    ])->and($request->uploadedFile('missing'))->toBe([]);

    unset($_FILES['media_file']);
});

it('captures uploaded files once at the fromGlobals boundary', function (): void {
    $server = $_SERVER;
    $files = $_FILES;

    try {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['REQUEST_URI'] = '/admin/media/upload';
        $_SERVER['HTTP_HOST'] = 'localhost';
        $_FILES = ['media_file' => ['name' => 'captured.jpg', 'error' => UPLOAD_ERR_OK]];

        $request = Request::fromGlobals();
        $_FILES['media_file']['name'] = 'mutated.jpg';

        expect($request->uploadedFile('media_file')['name'])->toBe('captured.jpg')
            ->and($request->withForm(['title' => 'x'])->uploadedFile('media_file')['name'])->toBe('captured.jpg')
            ->and($request->withRouteParams(['id' => 10])->uploadedFile('media_file')['name'])->toBe('captured.jpg');
    } finally {
        $_SERVER = $server;
        $_FILES = $files;
    }
});










