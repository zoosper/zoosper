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
