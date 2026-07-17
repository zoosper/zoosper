<?php

declare(strict_types=1);

namespace Zoosper\Media\Tests\Unit\Service;

use RuntimeException;
use Zoosper\Media\Service\MediaStorage;

test('stores validated media outside public and publishes controlled copy', function () {
    $root = sys_get_temp_dir() . '/zoosper-media-storage-' . bin2hex(random_bytes(4));
    mkdir($root, 0775, true);
    $tmp = $root . '/upload.png';
    file_put_contents($tmp, 'image-bytes');

    $stored = (new MediaStorage($root))->store(['tmp_name' => $tmp], 'png');

    expect($stored->storagePath)->toStartWith('storage/media/original/');
    expect($stored->publicPath)->toStartWith('/media/');
    expect(is_file($root . '/' . $stored->storagePath))->toBeTrue();
    expect(is_file($root . '/public' . $stored->publicPath))->toBeTrue();
});

test('rejects private storage paths under public', function () {
    $storage = new MediaStorage(sys_get_temp_dir());

    expect(fn () => $storage->absolutePath('public/media/file.png'))
        ->toThrow(RuntimeException::class);
});

test('rejects path traversal', function () {
    $storage = new MediaStorage(sys_get_temp_dir());

    expect(fn () => $storage->absolutePath('../secrets/file.png'))
        ->toThrow(RuntimeException::class);
});
