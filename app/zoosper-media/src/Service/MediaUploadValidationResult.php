<?php

declare(strict_types=1);

namespace Zoosper\Media\Service;

/**
 * Result object for an uploaded media file validation attempt.
 */
final readonly class MediaUploadValidationResult
{
    /** @param list<string> $errors */
    private function __construct(
        public bool $valid,
        public array $errors,
        public ?string $extension = null,
        public ?string $mimeType = null,
        public ?int $sizeBytes = null,
    ) {
    }

    public static function ok(string $extension, string $mimeType, int $sizeBytes): self
    {
        return new self(true, [], $extension, $mimeType, $sizeBytes);
    }

    /** @param list<string> $errors */
    public static function fail(array $errors): self
    {
        return new self(false, $errors);
    }
}
