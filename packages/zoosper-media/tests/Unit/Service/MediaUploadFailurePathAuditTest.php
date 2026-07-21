<?php

declare(strict_types=1);

namespace Zoosper\Media\Tests\Unit\Service;

test('media upload failure path audit locks storage persistence and cleanup semantics', function () {
    $root = dirname(__DIR__, 3);
    $source = (string) file_get_contents($root . '/tools/audit-media-upload-failure-path.php');

    expect($source)->toContain('media upload failure-path audit');
    expect($source)->toContain('upload service stores before repository persistence');
    expect($source)->toContain('upload service delegates cleanup to cleanup service');
    expect($source)->toContain('cleanup service refuses outside-root paths');
    expect($source)->toContain('normal admin upload delegates to shared service');
    expect($source)->toContain('editorjs upload delegates to shared service');
});

test('media upload failure path audit escapes source variable names in string probes', function () {
    $root = dirname(__DIR__, 3);
    $source = (string) file_get_contents($root . '/tools/audit-media-upload-failure-path.php');

    expect($source)->toContain('str_starts_with(\\$storedPath');
    expect($source)->toContain('\'/public\' . \\$storedPath');
});

test('media upload service source contains the required db failure cleanup contract', function () {
    $root = dirname(__DIR__, 3);
    $source = (string) file_get_contents($root . '/src/Service/MediaUploadService.php');

    expect($source)->toContain('$this->storage->store');
    expect($source)->toContain('$this->assets->create');
    expect($source)->toContain('catch (Throwable $exception)');
    expect($source)->toContain('if (is_object($stored))');
    expect($source)->toContain('$this->cleanup->cleanup($stored)');
    expect($source)->toContain('cleanup_deleted');
    expect($source)->toContain('cleanup_skipped');
    expect($source)->toContain("MediaUploadServiceResult::failure('Unable to store uploaded media file.', 500)");
});
