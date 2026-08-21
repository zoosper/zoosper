<?php

declare(strict_types=1);

namespace Zoosper\Media\Tests\Unit\Service;

use Zoosper\Media\Service\MediaUploadValidator;

function mediaTestPng(): string
{
    $file = tempnam(sys_get_temp_dir(), 'zoosper-media-') . '.png';
    file_put_contents($file, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/p9sAAAAASUVORK5CYII=', true));

    return $file;
}

test('accepts a small valid png upload', function () {
    $path = mediaTestPng();
    $result = (new MediaUploadValidator())->validate([
        'name' => 'pixel.png',
        'tmp_name' => $path,
        'size' => filesize($path),
        'error' => UPLOAD_ERR_OK,
    ]);

    expect($result->valid)->toBeTrue();
    expect($result->extension)->toBe('png');
    expect($result->mimeType)->toBe('image/png');
});

test('rejects unsupported extensions before storage', function () {
    $path = tempnam(sys_get_temp_dir(), 'zoosper-media-');
    file_put_contents($path, '<?php echo "bad";');

    $result = (new MediaUploadValidator())->validate([
        'name' => 'shell.php',
        'tmp_name' => $path,
        'size' => filesize($path),
        'error' => UPLOAD_ERR_OK,
    ]);

    expect($result->valid)->toBeFalse();
    expect($result->errors)->not->toBe([]);
});


test('accepts a small valid jpeg upload', function () {
    $path = tempnam(sys_get_temp_dir(), 'zoosper-media-');
    $image = imagecreatetruecolor(2, 2);
    imagejpeg($image, $path, 90);
    unset($image);

    $result = (new MediaUploadValidator())->validate([
        'name' => 'photo.jpg',
        'tmp_name' => $path,
        'size' => filesize($path),
        'error' => UPLOAD_ERR_OK,
    ]);

    expect($result->valid)->toBeTrue()
        ->and($result->extension)->toBe('jpg')
        ->and($result->mimeType)->toBe('image/jpeg');

    unlink($path);
});
