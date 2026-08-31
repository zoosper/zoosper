<?php

declare(strict_types=1);

namespace Zoosper\Media\Service;

/**
 * Result returned by the shared media upload service.
 *
 * TYPE-SAFETY FIX (confirmed 2026-07-30, external reviewer pass): $stored
 * was previously typed `?object` instead of the concrete, already-existing
 * StoredMediaFile class. Combined with `(string) $result->stored?->publicPath`
 * in MediaEditorJsUploadController, a null $stored would have silently
 * degraded to an empty string in a "successful" JSON response instead of
 * surfacing as an error — masking an impossible-but-unguarded state. Typing
 * $stored as ?StoredMediaFile does not, by itself, prevent that null state
 * from occurring (PHP's type system doesn't correlate `$successful === true`
 * with `$stored !== null` at the language level) — see
 * MediaEditorJsUploadController's own fix for the explicit runtime
 * invariant check that actually closes this gap. This type fix makes the
 * intended shape self-documenting and IDE/static-analysis-checkable, which
 * `?object` did not.
 */
final readonly class MediaUploadServiceResult
{
    /** @param array<string, mixed> $metadata */
    private function __construct(
        public bool $successful,
        public int $statusCode,
        public string $message,
        public ?int $assetId = null,
        public ?StoredMediaFile $stored = null,
        public array $metadata = [],
    ) {
    }

    /** @param array<string, mixed> $metadata */
    public static function success(int $assetId, StoredMediaFile $stored, array $metadata): self
    {
        return new self(true, 200, '', $assetId, $stored, $metadata);
    }

    public static function failure(string $message, int $statusCode = 422): self
    {
        return new self(false, $statusCode, $message);
    }
}











