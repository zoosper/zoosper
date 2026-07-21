<?php

declare(strict_types=1);

namespace Zoosper\Media\Processing;

/**
 * Resolved filesystem and public-path target for a generated media derivative.
 */
final readonly class LocalMediaDerivativePath
{
    public function __construct(
        public string $relativePath,
        public string $absolutePath,
        public string $publicPath,
    ) {
    }
}
