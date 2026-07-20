<?php

declare(strict_types=1);

namespace Zoosper\Media\Service;

use Throwable;
use Zoosper\Auth\Model\AdminUser;
use Zoosper\Core\Log\ErrorHandler;
use Zoosper\Media\Repository\MediaAssetRepository;

/**
 * Shared upload orchestration for all admin media upload entry points.
 *
 * This service centralises validation, storage, metadata persistence and cleanup.
 * If disk storage succeeds but the database insert fails, the just-written files
 * are removed before the exception is reported. This prevents orphaned private
 * and public media files.
 */
final readonly class MediaUploadService
{
    public function __construct(
        private MediaAssetRepository $assets,
        private MediaUploadValidator $validator,
        private MediaStorage $storage,
        private string $basePath,
        private ?ErrorHandler $errorHandler = null,
    ) {
    }

    /** @param array<string, mixed> $file */
    public function upload(array $file, AdminUser $user): MediaUploadServiceResult
    {
        $validation = $this->validator->validate($file);

        if (!$validation->valid) {
            return MediaUploadServiceResult::failure(implode(' ', $validation->errors), 422);
        }

        $stored = null;
        try {
            $stored = $this->storage->store($file, (string) $validation->extension);
            $assetId = $this->assets->create(
                uuid: $stored->uuid,
                filename: $stored->filename,
                originalFilename: $this->normaliseOriginalFilename((string) ($file['name'] ?? 'upload')),
                mimeType: (string) $validation->mimeType,
                extension: (string) $validation->extension,
                sizeBytes: (int) $validation->sizeBytes,
                storagePath: $stored->storagePath,
                publicPath: $stored->publicPath,
                createdBy: $user->id,
            );
        } catch (Throwable $exception) {
            if (is_object($stored)) {
                $this->cleanupStoredFiles($stored, $exception);
            }

            $this->errorHandler?->logException($exception, [
                'service' => 'MediaUploadService',
                'action' => 'upload',
                'cleanup_attempted' => is_object($stored),
            ]);

            return MediaUploadServiceResult::failure('Unable to store uploaded media file.', 500);
        }

        return MediaUploadServiceResult::success($assetId, $stored, [
            'id' => $assetId,
            'uuid' => $stored->uuid,
            'name' => $stored->filename,
            'mimeType' => (string) $validation->mimeType,
            'size' => (int) $validation->sizeBytes,
        ]);
    }

    private function normaliseOriginalFilename(string $filename): string
    {
        $filename = trim(str_replace(['\\', '/'], '-', $filename));
        $filename = preg_replace('/[^A-Za-z0-9._-]+/', '-', $filename) ?: 'upload';

        return mb_substr($filename, 0, 255);
    }

    private function cleanupStoredFiles(object $stored, Throwable $reason): void
    {
        foreach (['storagePath', 'publicPath'] as $property) {
            if (!isset($stored->{$property}) || !is_string($stored->{$property})) {
                continue;
            }

            foreach ($this->candidatePaths($stored->{$property}) as $path) {
                $this->safeUnlink($path, $reason);
            }
        }
    }

    /** @return list<string> */
    private function candidatePaths(string $storedPath): array
    {
        $storedPath = trim($storedPath);
        if ($storedPath === '') {
            return [];
        }

        $candidates = [];
        if (str_starts_with($storedPath, '/')) {
            $candidates[] = $this->basePath . '/public' . $storedPath;
            $candidates[] = $this->basePath . $storedPath;
        } else {
            $candidates[] = $this->basePath . '/' . $storedPath;
            if (str_starts_with($storedPath, 'media/')) {
                $candidates[] = $this->basePath . '/public/' . $storedPath;
            }
        }

        return array_values(array_unique($candidates));
    }

    private function safeUnlink(string $path, Throwable $reason): void
    {
        $base = realpath($this->basePath);
        $real = realpath($path);
        if ($base === false || $real === false || !str_starts_with($real, $base . DIRECTORY_SEPARATOR)) {
            return;
        }

        if (is_file($real)) {
            @unlink($real);
        }
    }
}
