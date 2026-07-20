<?php

declare(strict_types=1);

namespace Zoosper\Media\Service;

/**
 * Result returned by the shared media upload service.
 */
final readonly class MediaUploadServiceResult
{
    /** @param array<string, mixed> $metadata */
    private function __construct(
        public bool $successful,
        public int $statusCode,
        public string $message,
        public ?int $assetId = null,
        public ?object $stored = null,
        public array $metadata = [],
    ) {
    }

    /** @param array<string, mixed> $metadata */
    public static function success(int $assetId, object $stored, array $metadata): self
    {
        return new self(true, 200, '', $assetId, $stored, $metadata);
    }

    public static function failure(string $message, int $statusCode = 422): self
    {
        return new self(false, $statusCode, $message);
    }
}
