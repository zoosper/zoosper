<?php

declare(strict_types=1);

namespace Zoosper\Media\Processing;

use Zoosper\Media\Model\MediaAsset;

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

    public function processAfterUpload(MediaAsset|string $assetOrStoragePath): MediaProcessingResult
    {
        $policy = $this->policy ?? new MediaUploadDerivativePolicy(false);
        if (!$policy->enabled()) {
            return MediaProcessingResult::success([]);
        }

        if ($assetOrStoragePath instanceof MediaAsset) {
            $plan = $this->plan ?? (new MediaProcessingPolicy())->defaultPlan();
            return $this->processor->process($assetOrStoragePath, $plan);
        }

        if (method_exists($this->processor, 'processStoragePath')) {
            return $this->processor->processStoragePath($assetOrStoragePath, $this->plan);
        }

        return MediaProcessingResult::failure([
            'Derivative processor requires a persisted MediaAsset instance for upload-time processing.',
        ]);
    }
}
