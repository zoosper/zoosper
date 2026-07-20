<?php

declare(strict_types=1);

namespace Zoosper\Media\Tests\Unit\Controller;

use Zoosper\Media\Service\MediaUploadService;

test('editorjs upload controller delegates upload persistence to shared service', function () {
    $root = dirname(__DIR__, 5);
    $source = (string) file_get_contents($root . '/packages/zoosper-media/src/Controller/MediaEditorJsUploadController.php');

    expect($source)->toContain(MediaUploadService::class);
    expect($source)->toContain('$this->uploads->upload');
    expect($source)->toContain('MediaUploadService $uploads = null');
    expect($source)->toContain('new MediaUploadService(');
    expect($source)->not->toContain('$this->storage->store($file');
    expect($source)->not->toContain('$this->assets->create(');
});
