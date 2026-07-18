<?php

declare(strict_types=1);

namespace Packages\zoospermedia\tests\Unit\EditorJs;

use Zoosper\Media\EditorJs\EditorJsImageUploadResponseFactory;

test('builds Editor.js image upload success payload', function () {
    $payload = (new EditorJsImageUploadResponseFactory())->success('/media/example.png', [
        'id' => 10,
        'name' => 'example.png',
    ]);

    expect($payload['success'])->toBe(1);
    expect($payload['file']['url'])->toBe('/media/example.png');
    expect($payload['file']['id'])->toBe(10);
});

test('builds Editor.js image upload failure payload', function () {
    $payload = (new EditorJsImageUploadResponseFactory())->failure('Upload failed.');

    expect($payload)->toBe([
        'success' => 0,
        'message' => 'Upload failed.',
    ]);
});
