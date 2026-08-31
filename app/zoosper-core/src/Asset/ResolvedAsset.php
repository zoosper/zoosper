<?php

declare(strict_types=1);

namespace Zoosper\Core\Asset;

/**
 * Immutable description of a successfully resolved asset file.
 */
final class ResolvedAsset
{
    public function __construct(
        public readonly string $absolutePath,
        public readonly string $mimeType,
        public readonly int $size,
        public readonly string $etag,
        public readonly int $lastModified,
    ) {
    }
}










