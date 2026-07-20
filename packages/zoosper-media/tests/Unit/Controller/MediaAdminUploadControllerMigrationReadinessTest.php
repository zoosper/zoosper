<?php

declare(strict_types=1);

namespace Zoosper\Media\Tests\Unit\Controller;

use Zoosper\Media\Service\MediaUploadService;

test('media admin upload duplication audit tool exists and describes migration target', function () {
    $root = dirname(__DIR__, 5);
    $source = (string) file_get_contents($root . '/tools/audit-media-upload-controller-duplication.php');

    expect($source)->toContain('Zoosper media upload controller duplication audit');
    expect($source)->toContain('MediaAdminController direct storage/assets calls');
    expect($source)->toContain('MediaEditorJsUploadController shared service delegation');
    expect($source)->toContain('MediaUploadService cleanup delegation');
    expect($source)->toContain('Migrate MediaAdminController::upload() to MediaUploadService');
});

test('media admin upload inspection tool replaces the removed temporary dump helper', function () {
    $root = dirname(__DIR__, 5);
    $source = (string) file_get_contents($root . '/tools/inspect-media-admin-upload-migration.php');

    expect($source)->toContain('MEDIA ADMIN UPLOAD MIGRATION INSPECTION');
    expect($source)->toContain('MediaAdminController exists');
    expect($source)->toContain('Admin controller directly calls storage->store');
    expect($source)->toContain('Admin controller directly calls assets->create');
    expect($source)->toContain('source only; no .env, uploaded media, secrets or table data read');
});

test('shared media upload service remains available for normal admin upload migration', function () {
    expect(class_exists(MediaUploadService::class))->toBeTrue();
});
