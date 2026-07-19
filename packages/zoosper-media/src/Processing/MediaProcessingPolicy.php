<?php

declare(strict_types=1);

namespace Zoosper\Media\Processing;

/**
 * Central media derivative policy.
 *
 * Phase 1.37n deliberately defines the contract and policy only. Actual GD,
 * Imagick, storage-driver or queue-backed workers should be introduced behind
 * MediaProcessorInterface in a later phase.
 */
final readonly class MediaProcessingPolicy
{
    public function originalsAreImmutable(): bool
    {
        return true;
    }

    public function originalStoragePrefix(): string
    {
        return 'storage/media/original';
    }

    public function derivativeStoragePrefix(): string
    {
        return 'storage/media/derivatives';
    }

    public function publicDerivativePrefix(): string
    {
        return 'media/cache';
    }

    public function queueRecommended(): bool
    {
        return true;
    }

    public function defaultPlan(): MediaDerivativePlan
    {
        return new MediaDerivativePlan(
            new MediaDerivativeProfile('thumb', 320, 240, 'webp', 82, 'cover'),
            new MediaDerivativeProfile('medium', 960, 720, 'webp', 84, 'contain'),
            new MediaDerivativeProfile('large', 1600, 1200, 'webp', 86, 'contain'),
        );
    }
}
