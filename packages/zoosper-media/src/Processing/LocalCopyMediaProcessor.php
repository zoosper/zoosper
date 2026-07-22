<?php

declare(strict_types=1);

namespace Zoosper\Media\Processing;

use ReflectionClass;
use ReflectionException;
use RuntimeException;
use Stringable;
use Zoosper\Media\Model\MediaAsset;

/**
 * Engine-free local media processor.
 *
 * This adapter intentionally performs no resize, crop, re-encode or metadata
 * stripping. It copies the already validated uploaded original into the local
 * derivative locations defined by MediaProcessingPolicy and the local derivative
 * path resolver. The copied derivative bytes are deliberately unchanged. It
 * gives Zoosper a real MediaProcessorInterface implementation for orchestration
 * and failure-path testing before optional GD/Imagick engines are introduced as
 * separate packages.
 */
final readonly class LocalCopyMediaProcessor implements MediaProcessorInterface
{
    private const DEFAULT_ORIGINAL_STORAGE_ROOT = 'storage/media/original';

    /** @var list<string> */
    private const PROFILE_NAME_ACCESSORS = ['name', 'key', 'code', 'handle', 'profile', 'id', 'getName', 'getKey', 'getCode', 'getHandle'];

    public function __construct(
        private string $basePath,
        private ?MediaProcessingPolicy $policy = null,
        private ?LocalMediaDerivativePathResolver $paths = null,
        private ?LocalMediaDerivativeWriter $writer = null,
    ) {
    }

    /**
     * Process a persisted media asset through the engine-free local copy adapter.
     */
    public function process(MediaAsset $asset, MediaDerivativePlan $plan): MediaProcessingResult
    {
        return $this->processStoragePath($this->storagePathFromAsset($asset), $plan);
    }

    /**
     * Convenience path used by package-local smoke tools and transitional upload
     * seam wiring while the repository-level asset hydration path is completed.
     * The bytes are deliberately unchanged; this method only materialises local
     * derivative copies for orchestration and smoke testing.
     */
    public function processStoragePath(string $storagePath, ?MediaDerivativePlan $plan = null): MediaProcessingResult
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
            foreach ($plan->profiles as $index => $profile) {
                $profileName = $this->profileName($profile, $index);
                $target = $paths->resolve($storagePath, $profileName);
                $written = $writer->write($target, $contents);
                $derivatives[$profileName] = $this->publicDerivativePath($target, $written);
            }

            return MediaProcessingResult::success($derivatives);
        } catch (RuntimeException $exception) {
            return MediaProcessingResult::failure([$exception->getMessage()]);
        }
    }

    private function profileName(mixed $profile, int|string $index): string
    {
        if (is_string($profile) && $profile !== '') {
            return $profile;
        }

        if (is_object($profile)) {
            foreach (self::PROFILE_NAME_ACCESSORS as $accessor) {
                if (method_exists($profile, $accessor)) {
                    $value = $profile->{$accessor}();
                    if (is_string($value) && $value !== '') {
                        return $value;
                    }
                }

                if (property_exists($profile, $accessor)) {
                    $value = $this->reflectPropertyValue($profile, $accessor);
                    if (is_string($value) && $value !== '') {
                        return $value;
                    }
                }
            }

            if ($profile instanceof Stringable) {
                $value = (string) $profile;
                if ($value !== '') {
                    return $value;
                }
            }
        }

        if (is_string($index) && $index !== '') {
            return $index;
        }

        return 'profile-' . (string) $index;
    }

    private function publicDerivativePath(LocalMediaDerivativePath $target, mixed $written): string
    {
        if (is_object($written) && isset($written->publicPath) && is_string($written->publicPath)) {
            return $written->publicPath;
        }

        return $target->publicPath;
    }

    private function reflectPropertyValue(object $object, string $property): mixed
    {
        try {
            $reflection = new ReflectionClass($object);
            if (!$reflection->hasProperty($property)) {
                return null;
            }

            $propertyReflection = $reflection->getProperty($property);
            return $propertyReflection->getValue($object);
        } catch (ReflectionException) {
            return null;
        }
    }

    private function storagePathFromAsset(MediaAsset $asset): string
    {
        if (isset($asset->storagePath) && is_string($asset->storagePath)) {
            return $asset->storagePath;
        }

        if (method_exists($asset, 'storagePath')) {
            $storagePath = $asset->storagePath();
            if (is_string($storagePath)) {
                return $storagePath;
            }
        }

        throw new RuntimeException('Media asset does not expose a storagePath value.');
    }

    private function absoluteOriginalPath(string $storagePath, MediaProcessingPolicy $policy): string
    {
        $storagePath = ltrim(str_replace('\\', '/', $storagePath), '/');
        if ($storagePath === '' || str_contains($storagePath, '..') || str_starts_with($storagePath, '/')) {
            throw new RuntimeException('Unsafe source media storage path.');
        }

        $originalRoot = $this->originalStorageRoot($policy);
        if ($originalRoot !== '' && !str_starts_with($storagePath, $originalRoot . '/')) {
            throw new RuntimeException('Source media path is not under the configured original storage root.');
        }

        return rtrim($this->basePath, '/') . '/' . $storagePath;
    }

    private function originalStorageRoot(MediaProcessingPolicy $policy): string
    {
        if (method_exists($policy, 'originalStorageRoot')) {
            $root = $policy->originalStorageRoot();
            if (is_string($root) && $root !== '') {
                return trim(str_replace('\\', '/', $root), '/');
            }
        }

        return self::DEFAULT_ORIGINAL_STORAGE_ROOT;
    }
}
