<?php

declare(strict_types=1);

namespace Zoosper\Media\Processing;

/**
 * Feature seam for derivative generation during upload.
 *
 * Processing is disabled by default because the first processor is an
 * engine-free local copy/no-op adapter. Projects can explicitly enable this seam
 * once they are ready to materialise derivatives during uploads, and later swap
 * the processor to GD, Imagick, queued workers, or cloud storage drivers.
 */
final readonly class MediaUploadDerivativePolicy
{
    public function __construct(private bool $enabled = false)
    {
    }

    public function enabled(): bool
    {
        return $this->enabled;
    }
}
