<?php

declare(strict_types=1);

namespace Zoosper\Media\Tests\Unit\Controller;

test('media admin upload migration tool is fail-safe and write-gated', function () {
    $root = dirname(__DIR__, 3);
    $source = (string) file_get_contents($root . '/tools/apply-admin-upload-service-migration.php');

    expect($source)->toContain('Dry run only. Re-run with --write to apply.');
    expect($source)->toContain('MediaAdminController.php');
    expect($source)->toContain('MediaUploadService.php');
    expect($source)->toContain('Could not locate public upload() method safely');
    expect($source)->toContain("'->storage->store'");
    expect($source)->toContain("'->assets->create'");
});

test('media admin upload migration tool targets shared upload service and preserves backups', function () {
    $root = dirname(__DIR__, 3);
    $source = (string) file_get_contents($root . '/tools/apply-admin-upload-service-migration.php');

    expect($source)->toContain('MediaUploadService');
    expect($source)->toContain('$this->uploads->upload');
    expect($source)->toContain('.phase137r3.bak');
    expect($source)->toContain('--write');
});
