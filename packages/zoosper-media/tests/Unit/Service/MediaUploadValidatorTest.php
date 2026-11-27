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

test('uses the actual size when omitted but rejects an explicitly empty upload', function () {
    $path = mediaTestPng();
    try {
        $file = ['name' => 'pixel.png', 'tmp_name' => $path, 'error' => UPLOAD_ERR_OK];
        $validator = new MediaUploadValidator();
        $fallback = $validator->validate($file);
        $empty = $validator->validate($file + ['size' => 0]);
        $oversized = $validator->validate($file + ['size' => 5_242_881]);

        expect($fallback->valid)->toBeTrue()
            ->and($fallback->sizeBytes)->toBe(filesize($path))
            ->and($empty->errors)->toContain('Uploaded file is empty.')
            ->and($oversized->errors)->toContain('Uploaded file exceeds the 5 MB limit.');
    } finally {
        unlink($path);
    }
});

test('enforces the size limit against the temporary file even when its reported size is smaller', function () {
    $path = mediaTestPng();
    try {
        $handle = fopen($path, 'ab');
        ftruncate($handle, 5_242_881);
        fclose($handle);
        clearstatcache(true, $path);

        $result = (new MediaUploadValidator())->validate([
            'name' => 'pixel.png',
            'tmp_name' => $path,
            'size' => 1,
            'error' => UPLOAD_ERR_OK,
        ]);

        expect($result->valid)->toBeFalse()
            ->and($result->errors)->toContain('Uploaded file exceeds the 5 MB limit.');
    } finally {
        unlink($path);
    }
});

test('records the actual temporary file size when reported metadata is inaccurate', function () {
    $path = mediaTestPng();
    try {
        $result = (new MediaUploadValidator())->validate([
            'name' => 'pixel.png',
            'tmp_name' => $path,
            'size' => 1,
            'error' => UPLOAD_ERR_OK,
        ]);

        expect($result->valid)->toBeTrue()
            ->and($result->sizeBytes)->toBe(filesize($path));
    } finally {
        unlink($path);
    }
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











