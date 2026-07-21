<?php

declare(strict_types=1);

namespace Zoosper\Media\Processing;

use RuntimeException;

/**
 * Engine-free local media processor.
 *
 * This adapter intentionally performs no resize, crop, re-encode or metadata
 * stripping. It copies the already validated uploaded original into the local
 * derivative locations defined by MediaProcessingPolicy and the local derivative
 * path resolver. It gives Zoosper a real MediaProcessorInterface implementation
 * for orchestration and failure-path testing before optional GD/Imagick engines
 * are introduced as separate packages.
 */
final readonly class LocalCopyMediaProcessor implements MediaProcessorInterface
{
    public function __construct(
        private string $basePath,
        private ?MediaProcessingPolicy $policy = null,
        private ?LocalMediaDerivativePathResolver $paths = null,
        private ?LocalMediaDerivativeWriter $writer = null,
    ) {
    }

    /**
     * Copy an original media file into all derivative slots in the supplied
     * processing plan. The bytes are deliberately unchanged.
     */
    public function process(string $storagePath, ?MediaDerivativePlan $plan = null): MediaProcessingResult
    {
        $policy = $this->policy ?? new MediaProcessingPolicy();
        $plan ??= $policy->defaultPlan();
        $paths = $this->paths ?? new LocalMediaDerivativePathResolver($this->basePath);
        $writer = $this->writer ?? new LocalMediaDerivativeWriter();

        try {
            $source = $this->absoluteOriginalPath($storagePath, $policy);
            if (!is_file($source)) {
                return MediaProcessingResult::failure(['Original media file does not exist: ' . $storagePath]);
            }

            $contents = file_get_contents($source);
            if ($contents === false || $contents === '') {
                return MediaProcessingResult::failure(['Original media file is empty or unreadable: ' . $storagePath]);
            }

            $derivatives = [];
            foreach ($plan->profiles as $profile) {
                $target = $paths->resolve($storagePath, $profile);
                $written = $writer->write($target, $contents);
                $derivatives[$profile->name] = $written->publicPath;
            }

            return MediaProcessingResult::success($derivatives);
        } catch (RuntimeException $exception) {
            return MediaProcessingResult::failure([$exception->getMessage()]);
        }
    }

    private function absoluteOriginalPath(string $storagePath, MediaProcessingPolicy $policy): string
    {
        $storagePath = ltrim(str_replace('\\', '/', $storagePath), '/');
        if ($storagePath === '' || str_contains($storagePath, '..') || str_starts_with($storagePath, '/')) {
            throw new RuntimeException('Unsafe source media storage path.');
        }

        $originalRoot = trim($policy->originalStorageRoot(), '/');
        if ($originalRoot !== '' && !str_starts_with($storagePath, $originalRoot . '/')) {
            throw new RuntimeException('Source media path is not under the configured original storage root.');
        }

        return rtrim($this->basePath, '/') . '/' . $storagePath;
    }
}
