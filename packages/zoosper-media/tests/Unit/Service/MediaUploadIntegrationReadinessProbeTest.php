<?php

declare(strict_types=1);

namespace Zoosper\Media\Tests\Unit\Service;

test('media upload integration readiness probe documents concrete seams', function () {
    $root = dirname(__DIR__, 3);
    $source = (string) file_get_contents($root . '/tools/probe-media-upload-integration-readiness.php');

    expect($source)->toContain('media upload integration-test readiness probe');
    expect($source)->toContain('MediaUploadService::class');
    expect($source)->toContain('MediaStorage::class');
    expect($source)->toContain('MediaAssetRepository::class');
    expect($source)->toContain('MediaUploadValidator::class');
    expect($source)->toContain('Required integration-test seams');
});

test('media upload integration readiness docs explain the next behavioural test target', function () {
    $root = dirname(__DIR__, 3);
    $doc = (string) file_get_contents($root . '/docs/architecture/media-upload-integration-readiness.md');

    expect($doc)->toContain('storage succeeds / repository fails');
    expect($doc)->toContain('MediaUploadService');
    expect($doc)->toContain('MediaStoredFileCleanupService');
});
