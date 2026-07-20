<?php

declare(strict_types=1);

namespace Zoosper\Media\Tests\Unit\Controller;

test('package-local migration audit verifies admin upload service delegation signals', function () {
    $root = dirname(__DIR__, 3);
    $source = (string) file_get_contents($root . '/tools/audit-admin-upload-service-migration.php');

    expect($source)->toContain('media admin upload service migration audit');
    expect($source)->toContain('admin controller delegates to MediaUploadService');
    expect($source)->toContain('admin controller no longer writes storage directly');
    expect($source)->toContain('admin controller no longer creates assets directly');
    expect($source)->toContain('upload service delegates cleanup on persistence failure');
});

test('migration audit checks both normal admin and editorjs upload paths', function () {
    $root = dirname(__DIR__, 3);
    $source = (string) file_get_contents($root . '/tools/audit-admin-upload-service-migration.php');

    expect($source)->toContain('MediaAdminController.php');
    expect($source)->toContain('MediaEditorJsUploadController.php');
    expect($source)->toContain('MediaStoredFileCleanupService.php');
    expect($source)->toContain('->cleanup->cleanup($stored)');
});
