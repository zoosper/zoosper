<?php

declare(strict_types=1);

namespace Zoosper\Media\Service;

/**
 * Removes media files that were written during an upload that later failed.
 *
 * The service is deliberately conservative. It only deletes files that resolve
 * under the configured project base path, and it understands both private
 * storage paths and public `/media/...` URLs.
 */
final readonly class MediaStoredFileCleanupService
{
    public function __construct(private string $basePath)
    {
    }

    public function cleanup(object $stored): MediaStoredFileCleanupResult
    {
        $deleted = [];
        $skipped = [];

        foreach (['storagePath', 'publicPath'] as $property) {
            if (!isset($stored->{$property}) || !is_string($stored->{$property})) {
                continue;
            }

            foreach ($this->candidatePaths($stored->{$property}) as $path) {
                $real = $this->safeRealpath($path);
                if ($real === null) {
                    $skipped[] = $path;
                    continue;
                }

                if (is_file($real) && @unlink($real)) {
                    $deleted[] = $real;
                    continue;
                }

                if (is_file($real)) {
                    $skipped[] = $real;
                }
            }
        }

        return new MediaStoredFileCleanupResult($deleted, $skipped);
    }

    /** @return list<string> */
    public function candidatePaths(string $storedPath): array
    {
        $storedPath = trim($storedPath);
        if ($storedPath === '') {
            return [];
        }

        $basePath = rtrim($this->basePath, '/\\');
        $candidates = [];

        if (str_starts_with($storedPath, '/media/')) {
            $candidates[] = $basePath . '/public' . $storedPath;
            return array_values(array_unique($candidates));
        }

        if (str_starts_with($storedPath, '/')) {
            $candidates[] = $basePath . $storedPath;
            return array_values(array_unique($candidates));
        }

        $candidates[] = $basePath . '/' . $storedPath;
        if (str_starts_with($storedPath, 'media/')) {
            $candidates[] = $basePath . '/public/' . $storedPath;
        }

        return array_values(array_unique($candidates));
    }

    private function safeRealpath(string $path): ?string
    {
        $base = realpath($this->basePath);
        $real = realpath($path);

        if ($base === false || $real === false) {
            return null;
        }

        if ($real !== $base && !str_starts_with($real, $base . DIRECTORY_SEPARATOR)) {
            return null;
        }

        return $real;
    }
}
