<?php

declare(strict_types=1);

namespace Zoosper\Media\Model;

/**
 * Immutable media asset record.
 *
 * storagePath is project-relative and points outside public/. publicPath is a
 * browser path only after the upload has passed validation and been published
 * under the controlled public/media namespace.
 */
final readonly class MediaAsset
{
    public function __construct(
        public int $id,
        public string $uuid,
        public string $filename,
        public string $originalFilename,
        public string $mimeType,
        public string $extension,
        public int $sizeBytes,
        public string $storagePath,
        public ?string $publicPath,
        public string $status,
        public ?int $createdBy = null,
        public ?string $createdAt = null,
        public ?string $updatedAt = null,
    ) {
    }
}
