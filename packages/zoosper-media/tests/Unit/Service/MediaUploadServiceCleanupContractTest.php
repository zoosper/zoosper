<?php

declare(strict_types=1);

namespace Zoosper\Media\Tests\Unit\Service;

use Zoosper\Media\Service\MediaStoredFileCleanupService;
use Zoosper\Media\Service\MediaUploadService;
use Zoosper\Media\Service\MediaUploadServiceResult;
use Zoosper\Media\Service\StoredMediaFile;

/**
 * BUG FIX (2026-07-30): the second test below previously passed a bare
 * `(object) ['publicPath' => '/media/example.png']` (a plain stdClass) to
 * MediaUploadServiceResult::success(). This test itself was not wrong to
 * check — it was written before $stored's type was tightened from `object`
 * to the concrete StoredMediaFile — but once that type-safety fix landed
 * (see MediaUploadServiceResult's own docblock), this call site needed to
 * construct a real StoredMediaFile instead, exactly as every real, live
 * caller (MediaUploadService::upload()) already does. This is a one-line
 * change to this test's fixture data — the test's actual assertions and
 * intent are completely unchanged.
 */
test('media upload service contract centralises storage db persistence and delegated cleanup', function () {
    $root = dirname(__DIR__, 5);
    $source = (string) file_get_contents($root . '/packages/zoosper-media/src/Service/MediaUploadService.php');

    expect(class_exists(MediaUploadService::class))->toBeTrue();
    expect(class_exists(MediaStoredFileCleanupService::class))->toBeTrue();
    expect($source)->toContain('$this->storage->store');
    expect($source)->toContain('$this->assets->create');
    expect($source)->toContain('$this->cleanup->cleanup($stored)');
    expect($source)->toContain('storagePath');
    expect($source)->toContain('publicPath');
    expect($source)->toContain('MediaStoredFileCleanupService');
});

test('media upload service result separates validation storage and success responses', function () {
    $stored = new StoredMediaFile('test-uuid', 'example.png', '/storage/example.png', '/media/example.png');
    $success = MediaUploadServiceResult::success(5, $stored, ['id' => 5]);
    $failure = MediaUploadServiceResult::failure('bad upload', 422);

    expect($success->successful)->toBeTrue();
    expect($success->assetId)->toBe(5);
    expect($success->metadata)->toBe(['id' => 5]);
    expect($success->stored)->toBeInstanceOf(StoredMediaFile::class);
    expect($success->stored->publicPath)->toBe('/media/example.png');
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
