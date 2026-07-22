<?php

declare(strict_types=1);

namespace Packages\zoospermedia\tests\Unit\Processing;

test('local copy derivative smoke tool enables derivative processing explicitly', function () {
    $root = dirname(__DIR__, 3);
    $source = (string) file_get_contents($root . '/tools/smoke-local-copy-derivative-generation.php');

    expect($source)->toContain('new MediaUploadDerivativePolicy(true)');
    expect($source)->toContain('new MediaUploadDerivativeDispatcher');
    expect($source)->toContain('new LocalCopyMediaProcessor');
    expect($source)->toContain('/var/smoke/media-derivatives');
    expect($source)->toContain('derivative files created');
});

test('local copy derivative smoke audit locks controlled smoke assumptions', function () {
    $root = dirname(__DIR__, 3);
    $source = (string) file_get_contents($root . '/tools/audit-local-copy-derivative-smoke.php');

    expect($source)->toContain('smoke enables policy explicitly');
    expect($source)->toContain('smoke writes under var smoke directory');
    expect($source)->toContain('smoke creates a tiny png fixture');
    expect($source)->toContain('Result: ');
});
