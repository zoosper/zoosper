<?php

declare(strict_types=1);

namespace Zoosper\Media\Processing;

use InvalidArgumentException;

/**
 * Resolves deterministic, traversal-safe derivative paths for local media files.
 *
 * This class intentionally does not perform image manipulation. It provides the
 * safe local filesystem convention that a concrete GD/Imagick processor can use
 * later behind MediaProcessorInterface.
 */
final readonly class LocalMediaDerivativePathResolver
{
    public function __construct(private string $basePath)
    {
    }

    public function resolve(string $storagePath, string $profile, ?string $extension = null): LocalMediaDerivativePath
    {
        $storagePath = $this->normaliseStoragePath($storagePath);
        $profile = $this->normaliseProfile($profile);
        $extension = $this->normaliseExtension($extension ?: pathinfo($storagePath, PATHINFO_EXTENSION));

        $hash = substr(hash('sha256', $storagePath . '|' . $profile), 0, 16);
        $relative = 'storage/media/derivatives/' . $profile . '/' . substr($hash, 0, 2) . '/' . substr($hash, 2, 2) . '/' . $hash . '.' . $extension;
        $absolute = rtrim($this->basePath, '/\\') . '/' . $relative;
        $public = '/media/derivatives/' . $profile . '/' . substr($hash, 0, 2) . '/' . substr($hash, 2, 2) . '/' . $hash . '.' . $extension;

        return new LocalMediaDerivativePath($relative, $absolute, $public);
    }

    private function normaliseStoragePath(string $storagePath): string
    {
        $storagePath = trim(str_replace('\\', '/', $storagePath));
        if ($storagePath === '') {
            throw new InvalidArgumentException('Storage path is required for media derivative resolution.');
        }

        if (str_contains($storagePath, '..') || str_starts_with($storagePath, '/')) {
            throw new InvalidArgumentException('Storage path must be a relative path without traversal segments.');
        }

        return ltrim($storagePath, '/');
    }

    private function normaliseProfile(string $profile): string
    {
        $profile = strtolower(trim($profile));
        if (!preg_match('/^[a-z0-9][a-z0-9_-]{0,63}$/', $profile)) {
            throw new InvalidArgumentException('Derivative profile must contain only lowercase letters, numbers, underscores or dashes.');
        }

        return $profile;
    }

    private function normaliseExtension(string $extension): string
    {
        $extension = strtolower(ltrim(trim($extension), '.'));
        if (!preg_match('/^[a-z0-9]{2,8}$/', $extension)) {
            throw new InvalidArgumentException('Derivative extension must be a short alphanumeric file extension.');
        }

        return $extension;
    }
}
