<?php

declare(strict_types=1);

use Zoosper\Media\Processing\GdMediaProcessor;
use Zoosper\Media\Processing\MediaDerivativePlan;
use Zoosper\Media\Processing\MediaDerivativeProfile;

it('creates bounded WebP derivatives privately and publicly without upscaling', function (): void {
    $root = sys_get_temp_dir() . '/zoosper-gd-derivative-' . bin2hex(random_bytes(5));
    $storagePath = 'storage/media/original/2026/08/example.png';
    $absolute = $root . '/' . $storagePath;
    mkdir(dirname($absolute), 0775, true);
    $image = imagecreatetruecolor(800, 600);
    imagealphablending($image, false);
    imagesavealpha($image, true);
    imagepng($image, $absolute);
    unset($image);

    $result = (new GdMediaProcessor($root))->processStoragePath($storagePath, new MediaDerivativePlan(
        new MediaDerivativeProfile('thumb', 320, 240, 'webp', 82, 'cover'),
        new MediaDerivativeProfile('large', 1600, 1200, 'webp', 86, 'contain'),
    ));

    expect($result->successful)->toBeTrue()->and(array_keys($result->derivatives))->toBe(['thumb', 'large']);
    foreach ($result->derivatives as $code => $publicPath) {
        $public = $root . '/public' . $publicPath;
        expect(is_file($public))->toBeTrue();
        $dimensions = getimagesize($public);
        expect($dimensions['mime'])->toBe('image/webp');
        if ($code === 'thumb') {
            expect([$dimensions[0], $dimensions[1]])->toBe([320, 240]);
        } else {
            expect([$dimensions[0], $dimensions[1]])->toBe([800, 600]);
        }
    }
});

it('rejects paths outside canonical original storage', function (): void {
    $result = (new GdMediaProcessor(sys_get_temp_dir()))->processStoragePath('../unsafe.png');
    expect($result->successful)->toBeFalse()->and($result->errors[0])->toContain('Unsafe source media storage path');
});

it('preserves transparency and computes width, contain and centered cover derivatives', function (): void {
    $root = sys_get_temp_dir() . '/zoosper-gd-fits-' . bin2hex(random_bytes(5));
    $storagePath = 'storage/media/original/test/source.png';
    $absolute = $root . '/' . $storagePath;
    mkdir(dirname($absolute), 0775, true);
    $image = imagecreatetruecolor(400, 200);
    imagealphablending($image, false);
    imagesavealpha($image, true);
    imagefill($image, 0, 0, imagecolorallocatealpha($image, 0, 0, 0, 127));
    imagefilledrectangle($image, 150, 0, 249, 199, imagecolorallocate($image, 255, 0, 0));
    imagepng($image, $absolute);

    $result = (new GdMediaProcessor($root))->processStoragePath($storagePath, new MediaDerivativePlan(
        new MediaDerivativeProfile('width', 100, 100, 'png', 82, 'width'),
        new MediaDerivativeProfile('contain', 100, 100, 'png', 82, 'contain'),
        new MediaDerivativeProfile('cover', 100, 100, 'png', 82, 'cover'),
    ));

    expect($result->successful)->toBeTrue();
    foreach (['width' => [100, 50], 'contain' => [100, 50], 'cover' => [100, 100]] as $code => $expected) {
        $path = $root . '/public' . $result->derivatives[$code];
        $dimensions = getimagesize($path);
        expect([$dimensions[0], $dimensions[1]])->toBe($expected);
        $derivative = imagecreatefrompng($path);
        $center = imagecolorsforindex($derivative, imagecolorat($derivative, 50, 25));
        $corner = imagecolorsforindex($derivative, imagecolorat($derivative, 0, 0));
        expect($center['red'])->toBe(255)->and($center['alpha'])->toBe(0)->and($corner['alpha'])->toBe(127);
    }
});

it('rejects invalid raster data before producing derivatives', function (): void {
    $root = sys_get_temp_dir() . '/zoosper-gd-invalid-' . bin2hex(random_bytes(5));
    $storagePath = 'storage/media/original/test/invalid.png';
    mkdir(dirname($root . '/' . $storagePath), 0775, true);
    file_put_contents($root . '/' . $storagePath, 'not an image');

    $result = (new GdMediaProcessor($root))->processStoragePath($storagePath);
    expect($result->successful)->toBeFalse()->and($result->errors[0])->toContain('not a valid raster image');
});











