<?php

declare(strict_types=1);

namespace Zoosper\Media\Tests\Unit\Controller;

test('media admin upload migration inspection tool documents the signals needed for safe migration', function () {
    $root = dirname(__DIR__, 5);
    $source = (string) file_get_contents($root . '/tools/inspect-media-admin-upload-migration.php');

    expect($source)->toContain('MEDIA ADMIN UPLOAD MIGRATION INSPECTION');
    expect($source)->toContain('MediaAdminController exists');
    expect($source)->toContain('Admin controller directly calls storage->store');
    expect($source)->toContain('Admin controller directly calls assets->create');
    expect($source)->toContain('CONSTRUCTOR SIGNATURE');
    expect($source)->toContain('UPLOAD METHOD');
    expect($source)->toContain('source only; no .env, uploaded media, secrets or table data read');
});
