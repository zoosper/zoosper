<?php

declare(strict_types=1);

namespace Zoosper\Media\Processing;

use GdImage;
use RuntimeException;
use Throwable;
use Zoosper\Media\Model\MediaAsset;

/** Generates bounded, deterministic raster derivatives from canonical originals. */
final readonly class GdMediaProcessor implements MediaProcessorInterface
{
    public function __construct(
        private string $basePath,
        private ?MediaProcessingPolicy $policy = null,
        private ?LocalMediaDerivativePathResolver $paths = null,
    ) {
    }

    public function process(MediaAsset $asset, MediaDerivativePlan $plan): MediaProcessingResult
    {
        return $this->processStoragePath($asset->storagePath, $plan);
    }

    public function processStoragePath(string $storagePath, ?MediaDerivativePlan $plan = null): MediaProcessingResult
    {
        $policy = $this->policy ?? new MediaProcessingPolicy();
        $plan ??= $policy->defaultPlan();
        $paths = $this->paths ?? new LocalMediaDerivativePathResolver($this->basePath);
        $written = [];
        $writtenFiles = [];

        try {
            $sourcePath = $this->sourcePath($storagePath, $policy);
            [$source, $sourceWidth, $sourceHeight] = $this->decode($sourcePath);

            try {
                foreach ($plan->profiles as $profile) {
                    $target = $paths->resolve($storagePath, $profile->code, $profile->format);
                    [$canvas, $width, $height] = $this->transform($source, $sourceWidth, $sourceHeight, $profile);
                    try {
                        $this->writeAtomically($canvas, $target->absolutePath, $profile);
                        $publicAbsolute = $this->publicAbsolutePath($target->publicPath);
                        $this->copyAtomically($target->absolutePath, $publicAbsolute);
                        $written[$profile->code] = $target->publicPath;
                        $writtenFiles[] = [$target->absolutePath, $publicAbsolute];
                    } finally {
                        unset($canvas);
                    }
                }
            } finally {
                unset($source);
            }

            return MediaProcessingResult::success($written);
        } catch (Throwable $exception) {
            foreach ($writtenFiles as [$privatePath, $publicPath]) {
                $this->unlinkIfPresent($privatePath);
                $this->unlinkIfPresent($publicPath);
            }

            return MediaProcessingResult::failure([$exception->getMessage()]);
        }
    }

    private function sourcePath(string $storagePath, MediaProcessingPolicy $policy): string
    {
        $normalised = ltrim(str_replace('\\', '/', $storagePath), '/');
        $prefix = trim($policy->originalStoragePrefix(), '/');
        if ($normalised === '' || str_contains($normalised, '..') || !str_starts_with($normalised, $prefix . '/')) {
            throw new RuntimeException('Unsafe source media storage path.');
        }

        $absolute = rtrim($this->basePath, '/') . '/' . $normalised;
        if (!is_file($absolute)) {
            throw new RuntimeException('Original media file does not exist: ' . $storagePath);
        }

        return $absolute;
    }

    /** @return array{GdImage, int, int} */
    private function decode(string $path): array
    {
        $dimensions = @getimagesize($path);
        if (!is_array($dimensions)) {
            throw new RuntimeException('Derivative source is not a valid raster image.');
        }

        $image = match ($dimensions[2] ?? null) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($path),
            IMAGETYPE_PNG => @imagecreatefrompng($path),
            IMAGETYPE_GIF => @imagecreatefromgif($path),
            IMAGETYPE_WEBP => @imagecreatefromwebp($path),
            default => false,
        };
        if (!$image instanceof GdImage) {
            throw new RuntimeException('Unable to decode canonical media for derivative processing.');
        }

        return [$image, (int) $dimensions[0], (int) $dimensions[1]];
    }

    /** @return array{GdImage, int, int} */
    private function transform(GdImage $source, int $sourceWidth, int $sourceHeight, MediaDerivativeProfile $profile): array
    {
        $targetWidth = min($profile->width, $sourceWidth);
        $targetHeight = min($profile->height, $sourceHeight);

        if ($profile->fit === 'width') {
            $targetWidth = min($profile->width, $sourceWidth);
            $targetHeight = max(1, (int) round($sourceHeight * ($targetWidth / $sourceWidth)));
            return [$this->resample($source, $sourceWidth, $sourceHeight, $targetWidth, $targetHeight, 0, 0, $sourceWidth, $sourceHeight, $profile), $targetWidth, $targetHeight];
        }

        if ($profile->fit === 'cover' && $sourceWidth >= $profile->width && $sourceHeight >= $profile->height) {
            $targetWidth = $profile->width;
            $targetHeight = $profile->height;
            $sourceRatio = $sourceWidth / $sourceHeight;
            $targetRatio = $targetWidth / $targetHeight;
            if ($sourceRatio > $targetRatio) {
                $cropHeight = $sourceHeight;
                $cropWidth = (int) round($sourceHeight * $targetRatio);
                $sourceX = (int) floor(($sourceWidth - $cropWidth) / 2);
                $sourceY = 0;
            } else {
                $cropWidth = $sourceWidth;
                $cropHeight = (int) round($sourceWidth / $targetRatio);
                $sourceX = 0;
                $sourceY = (int) floor(($sourceHeight - $cropHeight) / 2);
            }
            return [$this->resample($source, $sourceWidth, $sourceHeight, $targetWidth, $targetHeight, $sourceX, $sourceY, $cropWidth, $cropHeight, $profile), $targetWidth, $targetHeight];
        }

        $scale = min($targetWidth / $sourceWidth, $targetHeight / $sourceHeight, 1);
        $targetWidth = max(1, (int) round($sourceWidth * $scale));
        $targetHeight = max(1, (int) round($sourceHeight * $scale));
        return [$this->resample($source, $sourceWidth, $sourceHeight, $targetWidth, $targetHeight, 0, 0, $sourceWidth, $sourceHeight, $profile), $targetWidth, $targetHeight];
    }

    private function resample(GdImage $source, int $sourceWidth, int $sourceHeight, int $targetWidth, int $targetHeight, int $sourceX, int $sourceY, int $copyWidth, int $copyHeight, MediaDerivativeProfile $profile): GdImage
    {
        $canvas = imagecreatetruecolor($targetWidth, $targetHeight);
        if (!$canvas instanceof GdImage) {
            throw new RuntimeException('Unable to allocate derivative image canvas.');
        }
        if (in_array($profile->format, ['png', 'webp'], true)) {
            imagealphablending($canvas, false);
            imagesavealpha($canvas, true);
            $transparent = imagecolorallocatealpha($canvas, 0, 0, 0, 127);
            imagefill($canvas, 0, 0, $transparent);
        } else {
            $white = imagecolorallocate($canvas, 255, 255, 255);
            imagefill($canvas, 0, 0, $white);
        }
        if (!imagecopyresampled($canvas, $source, 0, 0, $sourceX, $sourceY, $targetWidth, $targetHeight, $copyWidth, $copyHeight)) {
            unset($canvas);
            throw new RuntimeException('Unable to resample media derivative.');
        }
        return $canvas;
    }

    private function writeAtomically(GdImage $image, string $path, MediaDerivativeProfile $profile): void
    {
        $this->ensureDirectory(dirname($path));
        $temporary = $path . '.tmp-' . bin2hex(random_bytes(6));
        try {
            $written = match ($profile->format) {
                'jpg', 'jpeg' => imagejpeg($image, $temporary, $profile->quality),
                'png' => imagepng($image, $temporary, 6),
                'webp' => imagewebp($image, $temporary, $profile->quality),
                default => false,
            };
            if (!$written || !rename($temporary, $path)) {
                throw new RuntimeException('Unable to publish media derivative: ' . $path);
            }
        } finally {
            $this->unlinkIfPresent($temporary);
        }
    }

    private function copyAtomically(string $source, string $destination): void
    {
        $this->ensureDirectory(dirname($destination));
        $temporary = $destination . '.tmp-' . bin2hex(random_bytes(6));
        try {
            if (!copy($source, $temporary) || !rename($temporary, $destination)) {
                throw new RuntimeException('Unable to publish public media derivative.');
            }
        } finally {
            $this->unlinkIfPresent($temporary);
        }
    }

    private function publicAbsolutePath(string $publicPath): string
    {
        $normalised = ltrim(str_replace('\\', '/', $publicPath), '/');
        if (!str_starts_with($normalised, 'media/derivatives/') || str_contains($normalised, '..')) {
            throw new RuntimeException('Unsafe public derivative path.');
        }
        return rtrim($this->basePath, '/') . '/public/' . $normalised;
    }

    private function unlinkIfPresent(string $path): void
    {
        if (is_file($path) && !unlink($path)) {
            throw new RuntimeException('Unable to remove temporary media derivative: ' . $path);
        }
    }

    private function ensureDirectory(string $directory): void
    {
        if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
            throw new RuntimeException('Unable to create derivative directory: ' . $directory);
        }
    }
}
