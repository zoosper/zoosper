<?php

declare(strict_types=1);

namespace Zoosper\Media\Tests\Unit\Service;

use RuntimeException;
use Zoosper\Media\Service\MediaCanonicalizerInterface;
use Zoosper\Media\Service\MediaStorage;

test('stores validated media outside public and publishes controlled copy', function () {
    $root = sys_get_temp_dir() . '/zoosper-media-storage-' . bin2hex(random_bytes(4));
    mkdir($root, 0775, true);
    $tmp = $root . '/upload.png';
    $image = imagecreatetruecolor(2, 2);
    imagealphablending($image, false);
    imagesavealpha($image, true);
    imagepng($image, $tmp);
    unset($image);

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

test('removes partial canonical output when the encoder throws an error', function () {
    $root = sys_get_temp_dir() . '/zoosper-media-storage-error-' . bin2hex(random_bytes(4));
    mkdir($root, 0775, true);
    $canonicalizer = new class implements MediaCanonicalizerInterface {
        public function canonicalize(string $sourcePath, string $destinationPath, string $extension): void
        {
            file_put_contents($destinationPath, 'partial output');
            throw new \Error('encoder failed');
        }
    };

    expect(fn () => (new MediaStorage($root, $canonicalizer))->store(['tmp_name' => 'unused'], 'png'))
        ->toThrow(\Error::class, 'encoder failed');

    $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($root, \FilesystemIterator::SKIP_DOTS));
    foreach ($files as $file) {
        expect($file->isFile())->toBeFalse();
    }
});

test('rejects path traversal', function () {
    $storage = new MediaStorage(sys_get_temp_dir());

    expect(fn () => $storage->absolutePath('../secrets/file.png'))
        ->toThrow(RuntimeException::class);
});











