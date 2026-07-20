<?php

declare(strict_types=1);

namespace Zoosper\Media\Tests\Unit\Service;

use Zoosper\Media\Service\MediaStoredFileCleanupService;
use Zoosper\Media\Service\MediaUploadService;

test('media upload service delegates orphan cleanup to cleanup service', function () {
    $root = dirname(__DIR__, 5);
    $source = (string) file_get_contents($root . '/packages/zoosper-media/src/Service/MediaUploadService.php');

    expect($source)->toContain('private MediaStoredFileCleanupService $cleanup');
    expect($source)->toContain('?MediaStoredFileCleanupService $cleanup = null');
    expect($source)->toContain('new MediaStoredFileCleanupService($basePath)');
    expect($source)->toContain('$this->cleanup->cleanup($stored)');
    expect($source)->toContain('cleanup_deleted');
    expect($source)->toContain('cleanup_skipped');
    expect(class_exists(MediaStoredFileCleanupService::class))->toBeTrue();
    expect(class_exists(MediaUploadService::class))->toBeTrue();
});

test('media service config registers explicit cleanup service', function () {
    $root = dirname(__DIR__, 5);
    $source = (string) file_get_contents($root . '/packages/zoosper-media/config/services.php');

    expect($source)->toContain(MediaStoredFileCleanupService::class);
    expect($source)->toContain('new MediaStoredFileCleanupService(dirname(__DIR__, 3))');
    expect($source)->toContain('cleanup: $services->get(MediaStoredFileCleanupService::class)');
});
