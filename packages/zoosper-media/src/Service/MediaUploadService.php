<?php

declare(strict_types=1);

namespace Zoosper\Media\Service;

use Zoosper\Media\Processing\MediaUploadDerivativeDispatcher;
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
    private MediaStoredFileCleanupService $cleanup;

    public function __construct(
        private MediaAssetRepository $assets,
        private MediaUploadValidator $validator,
        private MediaStorage $storage,
        private string $basePath,
        private ?ErrorHandler $errorHandler = null,
        ?MediaStoredFileCleanupService $cleanup = null,
    
        private ?MediaUploadDerivativeDispatcher $derivatives = null
    ) {
        $this->cleanup = $cleanup ?? new MediaStoredFileCleanupService($basePath);
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

            $this->derivatives?->processAfterUpload($stored->storagePath);
        } catch (Throwable $exception) {
            $cleanupResult = null;
            if (is_object($stored)) {
                $cleanupResult = $this->cleanup->cleanup($stored);
            }

            $this->errorHandler?->logException($exception, [
                'service' => 'MediaUploadService',
                'action' => 'upload',
                'cleanup_attempted' => is_object($stored),
                'cleanup_deleted' => $cleanupResult?->deletedCount() ?? 0,
                'cleanup_skipped' => $cleanupResult?->skippedCount() ?? 0,
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
}
