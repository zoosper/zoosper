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
