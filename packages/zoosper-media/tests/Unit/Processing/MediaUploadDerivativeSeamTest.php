<?php

declare(strict_types=1);

namespace Packages\zoospermedia\tests\Unit\Processing;

use Zoosper\Media\Processing\MediaUploadDerivativeDispatcher;
use Zoosper\Media\Processing\MediaUploadDerivativePolicy;

test('upload derivative dispatcher and policy are available', function () {
    expect(class_exists(MediaUploadDerivativeDispatcher::class))->toBeTrue();
    expect(class_exists(MediaUploadDerivativePolicy::class))->toBeTrue();
});

test('upload derivative processing seam is disabled by default', function () {
    $root = dirname(__DIR__, 3);
    $policy = (string) file_get_contents($root . '/src/Processing/MediaUploadDerivativePolicy.php');
    $dispatcher = (string) file_get_contents($root . '/src/Processing/MediaUploadDerivativeDispatcher.php');

    expect($policy)->toContain('private bool $enabled = false');
    expect($dispatcher)->toContain('new MediaUploadDerivativePolicy(false)');
    expect($dispatcher)->toContain('MediaProcessingResult::success([])');
});

test('upload derivative seam migration helper is write gated', function () {
    $root = dirname(__DIR__, 3);
    $tool = (string) file_get_contents($root . '/tools/apply-upload-derivative-processing-seam.php');

    expect($tool)->toContain('Dry run only. Re-run with --write to apply.');
    expect($tool)->toContain('MediaUploadDerivativeDispatcher');
    expect($tool)->toContain('processAfterUpload($stored->storagePath');
    expect($tool)->toContain('.phase137n3.bak');
});
