<?php

declare(strict_types=1);

namespace Zoosper\Media\Processing;

/**
 * Upload-time derivative orchestration seam.
 *
 * The dispatcher keeps the upload service independent from concrete image
 * engines. When the policy is disabled it returns an empty successful result;
 * when enabled it delegates to the configured MediaProcessorInterface.
 */
final readonly class MediaUploadDerivativeDispatcher
{
    public function __construct(
        private MediaProcessorInterface $processor,
        private ?MediaUploadDerivativePolicy $policy = null,
        private ?MediaDerivativePlan $plan = null,
    ) {
    }

    public function processAfterUpload(string $storagePath): MediaProcessingResult
    {
        $policy = $this->policy ?? new MediaUploadDerivativePolicy(false);
        if (!$policy->enabled()) {
            return MediaProcessingResult::success([]);
        }

        return $this->processor->process($storagePath, $this->plan);
    }
}
