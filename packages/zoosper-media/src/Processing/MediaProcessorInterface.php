<?php

declare(strict_types=1);

namespace Zoosper\Media\Processing;

use Zoosper\Media\Model\MediaAsset;

/**
 * Contract for future media derivative processors.
 *
 * Implementations may be synchronous initially or queue-backed later. Callers
 * should depend on this contract instead of a concrete GD/Imagick/remote worker.
 */
interface MediaProcessorInterface
{
    public function process(MediaAsset $asset, MediaDerivativePlan $plan): MediaProcessingResult;
}
