<?php

declare(strict_types=1);

namespace Zoosper\Media\Processing;

use InvalidArgumentException;

/**
 * Immutable description of one generated media derivative.
 *
 * The profile is policy only; it does not process images. Processing engines can
 * use it later to create thumbnails, WebP copies or other derived assets while
 * keeping uploaded originals immutable.
 */
final readonly class MediaDerivativeProfile
{
    public function __construct(
        public string $code,
        public int $width,
        public int $height,
        public string $format = 'webp',
        public int $quality = 82,
        public string $fit = 'contain',
    ) {
        if (!preg_match('/^[a-z0-9][a-z0-9_-]*$/', $code)) {
            throw new InvalidArgumentException('Media derivative profile code must be kebab/snake safe: ' . $code);
        }
        if ($width < 1 || $height < 1) {
            throw new InvalidArgumentException('Media derivative dimensions must be positive.');
        }
        if ($quality < 1 || $quality > 100) {
            throw new InvalidArgumentException('Media derivative quality must be between 1 and 100.');
        }
        if (!in_array($format, ['jpg', 'jpeg', 'png', 'webp'], true)) {
            throw new InvalidArgumentException('Unsupported media derivative format: ' . $format);
        }
        if (!in_array($fit, ['contain', 'cover', 'width'], true)) {
            throw new InvalidArgumentException('Unsupported media derivative fit mode: ' . $fit);
        }
    }
}
