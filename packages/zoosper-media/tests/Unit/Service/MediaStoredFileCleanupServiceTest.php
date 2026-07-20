<?php

declare(strict_types=1);

namespace Zoosper\Media\Tests\Unit\Service;

use Zoosper\Media\Service\MediaStoredFileCleanupService;

test('cleanup service removes private and public files for failed uploads', function () {
    $root = sys_get_temp_dir() . '/zoosper-media-cleanup-' . bin2hex(random_bytes(4));
    mkdir($root . '/storage/media/original/2026/07', 0775, true);
    mkdir($root . '/public/media/2026/07', 0775, true);

    $private = $root . '/storage/media/original/2026/07/example.png';
    $public = $root . '/public/media/2026/07/example.png';
    file_put_contents($private, 'private');
    file_put_contents($public, 'public');

    $result = (new MediaStoredFileCleanupService($root))->cleanup((object) [
        'storagePath' => 'storage/media/original/2026/07/example.png',
        'publicPath' => '/media/2026/07/example.png',
    ]);

    expect($result->deletedCount())->toBe(2);
    expect(is_file($private))->toBeFalse();
    expect(is_file($public))->toBeFalse();
});

test('cleanup service refuses to delete paths outside the project root', function () {
    $root = sys_get_temp_dir() . '/zoosper-media-cleanup-' . bin2hex(random_bytes(4));
    mkdir($root, 0775, true);
    $outside = sys_get_temp_dir() . '/zoosper-outside-' . bin2hex(random_bytes(4)) . '.txt';
    file_put_contents($outside, 'outside');

    $result = (new MediaStoredFileCleanupService($root))->cleanup((object) [
        'storagePath' => $outside,
    ]);

    expect(is_file($outside))->toBeTrue();
    expect($result->deletedCount())->toBe(0);
});

test('cleanup service maps public media URLs to public media files', function () {
    $root = sys_get_temp_dir() . '/zoosper-media-cleanup-' . bin2hex(random_bytes(4));
    $service = new MediaStoredFileCleanupService($root);

    expect($service->candidatePaths('/media/2026/07/example.png'))
        ->toBe([$root . '/public/media/2026/07/example.png']);
});
