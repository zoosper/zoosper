<?php

declare(strict_types=1);

namespace Packages\zoospermedia\tests\Unit\Processing;

use ReflectionMethod;
use Zoosper\Media\Model\MediaAsset;
use Zoosper\Media\Processing\LocalCopyMediaProcessor;
use Zoosper\Media\Processing\MediaDerivativePlan;
use Zoosper\Media\Processing\MediaProcessorInterface;

test('local copy media processor signature matches media processor interface', function () {
    $interface = new ReflectionMethod(MediaProcessorInterface::class, 'process');
    $processor = new ReflectionMethod(LocalCopyMediaProcessor::class, 'process');

    expect((string) $processor->getParameters()[0]->getType())->toBe((string) $interface->getParameters()[0]->getType());
    expect((string) $processor->getParameters()[0]->getType())->toBe(MediaAsset::class);
    expect((string) $processor->getParameters()[1]->getType())->toBe(MediaDerivativePlan::class);
    expect((string) $processor->getReturnType())->toBe((string) $interface->getReturnType());
});

test('local copy processor keeps a transitional storage path helper for package smoke tools', function () {
    expect(method_exists(LocalCopyMediaProcessor::class, 'processStoragePath'))->toBeTrue();
});

test('local copy processor validates an asset storage path before reading it', function () {
    $asset = new MediaAsset(1, 'uuid', 'image.png', 'image.png', 'image/png', 'png', 1, '../image.png', null, 'active');

    $result = (new LocalCopyMediaProcessor(sys_get_temp_dir()))->process($asset, new MediaDerivativePlan());

    expect($result->successful)->toBeFalse()
        ->and($result->errors)->toContain('Unsafe source media storage path.');
});











