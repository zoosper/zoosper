<?php

declare(strict_types=1);

namespace Zoosper\Media\Service;

use RuntimeException;

/**
 * Stores validated media uploads outside public/ and publishes a controlled copy.
 *
 * Originals live under storage/media/original. The browser-facing copy lives
 * under public/media only after validation, matching the public webroot policy.
 */
final readonly class MediaStorage
{
    public function __construct(private string $basePath)
    {
    }

    /**
     * @param array<string, mixed> $file One validated $_FILES entry.
     */
    public function store(array $file, string $extension): StoredMediaFile
    {
        $uuid = bin2hex(random_bytes(16));
        $filename = $uuid . '.' . strtolower($extension);
        $datePath = gmdate('Y/m');
        $storageRelative = 'storage/media/original/' . $datePath . '/' . $filename;
        $publicRelative = 'public/media/' . $datePath . '/' . $filename;

        $storagePath = $this->absolutePath($storageRelative, false);
        $publicPath = $this->absolutePath($publicRelative, true);
        $tmpName = (string) ($file['tmp_name'] ?? '');

        $this->ensureDirectory(dirname($storagePath));
        $this->ensureDirectory(dirname($publicPath));

        if (!@copy($tmpName, $storagePath)) {
            throw new RuntimeException('Unable to store uploaded media file.');
        }

        if (!@copy($storagePath, $publicPath)) {
            @unlink($storagePath);
            throw new RuntimeException('Unable to publish uploaded media file.');
        }

        return new StoredMediaFile(
            uuid: $uuid,
            filename: $filename,
            storagePath: $storageRelative,
            publicPath: '/' . ltrim(str_replace('public/', '', $publicRelative), '/'),
        );
    }

    public function absolutePath(string $relativePath, bool $allowPublic = false): string
    {
        $relativePath = ltrim(str_replace('\\', '/', $relativePath), '/');
        if (str_contains($relativePath, '..')) {
            throw new RuntimeException('Unsafe media path traversal detected.');
        }

        if (!$allowPublic && str_starts_with($relativePath, 'public/')) {
            throw new RuntimeException('Private media storage paths must not be under public/.');
        }

        return rtrim($this->basePath, '/') . '/' . $relativePath;
    }

    private function ensureDirectory(string $directory): void
    {
        if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
            throw new RuntimeException('Unable to create media directory: ' . $directory);
        }
    }
}
