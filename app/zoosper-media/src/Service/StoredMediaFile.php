<?php

declare(strict_types=1);

namespace Zoosper\Media\Service;

/**
 * Result of storing a validated media upload.
 */
final readonly class StoredMediaFile
{
    public function __construct(
        public string $uuid,
        public string $filename,
        public string $storagePath,
        public string $publicPath,
    ) {
    }
}
