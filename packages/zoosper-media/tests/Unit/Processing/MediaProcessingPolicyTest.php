<?php

declare(strict_types=1);

namespace Zoosper\Media\Tests\Unit\Processing;

use InvalidArgumentException;
use Zoosper\Media\Processing\MediaDerivativePlan;
use Zoosper\Media\Processing\MediaDerivativeProfile;
use Zoosper\Media\Processing\MediaProcessingPolicy;
use Zoosper\Media\Processing\MediaProcessingResult;
use Zoosper\Media\Processing\MediaProcessorInterface;

test('media processing policy keeps originals immutable and defines derivative paths', function () {
    $policy = new MediaProcessingPolicy();

    expect($policy->originalsAreImmutable())->toBeTrue();
    expect($policy->originalStoragePrefix())->toBe('storage/media/original');
    expect($policy->derivativeStoragePrefix())->toBe('storage/media/derivatives');
    expect($policy->publicDerivativePrefix())->toBe('media/cache');
    expect($policy->queueRecommended())->toBeTrue();
});

test('media processing policy defines stable default derivative profiles', function () {
    $plan = (new MediaProcessingPolicy())->defaultPlan();

    expect($plan)->toBeInstanceOf(MediaDerivativePlan::class);
    expect($plan->isEmpty())->toBeFalse();
    expect($plan->codes())->toBe(['thumb', 'medium', 'large']);
    expect($plan->profiles[0]->format)->toBe('webp');
    expect($plan->profiles[0]->fit)->toBe('cover');
});

test('media derivative profiles validate unsafe policy data early', function () {
    expect(fn () => new MediaDerivativeProfile('Bad Code!', 100, 100))
        ->toThrow(InvalidArgumentException::class);

    expect(fn () => new MediaDerivativeProfile('thumb', 0, 100))
        ->toThrow(InvalidArgumentException::class);

    expect(fn () => new MediaDerivativeProfile('thumb', 100, 100, 'svg'))
        ->toThrow(InvalidArgumentException::class);
});

test('media processing result records success and failure contracts', function () {
    $success = MediaProcessingResult::success(['thumb' => '/media/cache/thumb/example.webp']);
    $failure = MediaProcessingResult::failure(['Processor unavailable.']);

    expect($success->successful)->toBeTrue();
    expect($success->derivatives['thumb'])->toBe('/media/cache/thumb/example.webp');
    expect($failure->successful)->toBeFalse();
    expect($failure->errors)->toBe(['Processor unavailable.']);
});

test('media processor interface is available for future queue or image engine implementations', function () {
    expect(interface_exists(MediaProcessorInterface::class))->toBeTrue();
});

test('media module registers media processing policy as a service', function () {
    $root = dirname(__DIR__, 5);
    $source = (string) file_get_contents($root . '/packages/zoosper-media/config/services.php');

    expect($source)->toContain(MediaProcessingPolicy::class);
    expect($source)->toContain('new MediaProcessingPolicy()');
});
