<?php

declare(strict_types=1);

namespace Zoosper\Media\Tests\Unit\Service;

use Zoosper\Media\Service\MediaUploadService;
use Zoosper\Media\Service\MediaUploadServiceResult;

test('media upload service contract centralises storage db persistence and cleanup', function () {
    $root = dirname(__DIR__, 5);
    $source = (string) file_get_contents($root . '/packages/zoosper-media/src/Service/MediaUploadService.php');

    expect(class_exists(MediaUploadService::class))->toBeTrue();
    expect($source)->toContain('$this->storage->store');
    expect($source)->toContain('$this->assets->create');
    expect($source)->toContain('cleanupStoredFiles');
    expect($source)->toContain('storagePath');
    expect($source)->toContain('publicPath');
    expect($source)->toContain('safeUnlink');
});

test('media upload service result separates validation storage and success responses', function () {
    $stored = (object) ['publicPath' => '/media/example.png'];
    $success = MediaUploadServiceResult::success(5, $stored, ['id' => 5]);
    $failure = MediaUploadServiceResult::failure('bad upload', 422);

    expect($success->successful)->toBeTrue();
    expect($success->assetId)->toBe(5);
    expect($success->metadata)->toBe(['id' => 5]);
    expect($failure->successful)->toBeFalse();
    expect($failure->statusCode)->toBe(422);
    expect($failure->message)->toBe('bad upload');
});

test('media module registers shared media upload service', function () {
    $root = dirname(__DIR__, 5);
    $source = (string) file_get_contents($root . '/packages/zoosper-media/config/services.php');

    expect($source)->toContain(MediaUploadService::class);
    expect($source)->toContain('new MediaUploadService(');
    expect($source)->toContain('basePath: dirname(__DIR__, 3)');
});
