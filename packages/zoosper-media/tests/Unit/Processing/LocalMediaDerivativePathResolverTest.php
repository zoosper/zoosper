<?php

declare(strict_types=1);

namespace Zoosper\Media\Tests\Unit\Processing;

use InvalidArgumentException;
use Zoosper\Media\Processing\LocalMediaDerivativePathResolver;
use Zoosper\Media\Processing\LocalMediaDerivativeWriter;

test('local derivative resolver creates deterministic private and public derivative paths', function () {
    $root = sys_get_temp_dir() . '/zoosper-derivative-' . bin2hex(random_bytes(4));
    $resolver = new LocalMediaDerivativePathResolver($root);

    $first = $resolver->resolve('storage/media/original/2026/07/example.png', 'admin_thumb');
    $second = $resolver->resolve('storage/media/original/2026/07/example.png', 'admin_thumb');

    expect($first->relativePath)->toBe($second->relativePath);
    expect($first->relativePath)->toStartWith('storage/media/derivatives/admin_thumb/');
    expect($first->absolutePath)->toStartWith($root . '/storage/media/derivatives/admin_thumb/');
    expect($first->publicPath)->toStartWith('/media/derivatives/admin_thumb/');
    expect($first->publicPath)->toEndWith('.png');
});

test('local derivative resolver rejects unsafe paths and profiles', function () {
    $resolver = new LocalMediaDerivativePathResolver(sys_get_temp_dir());

    expect(fn () => $resolver->resolve('../secret.png', 'admin_thumb'))->toThrow(InvalidArgumentException::class);
    expect(fn () => $resolver->resolve('/absolute/secret.png', 'admin_thumb'))->toThrow(InvalidArgumentException::class);
    expect(fn () => $resolver->resolve('storage/media/original/example.png', '../bad'))->toThrow(InvalidArgumentException::class);
});

test('local derivative writer creates directories and writes derivative bytes', function () {
    $root = sys_get_temp_dir() . '/zoosper-derivative-' . bin2hex(random_bytes(4));
    $path = (new LocalMediaDerivativePathResolver($root))->resolve('storage/media/original/example.png', 'admin_thumb');

    (new LocalMediaDerivativeWriter())->write($path, 'image-bytes');

    expect(is_file($path->absolutePath))->toBeTrue();
    expect(file_get_contents($path->absolutePath))->toBe('image-bytes');
});
