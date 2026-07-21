<?php

declare(strict_types=1);

namespace Zoosper\Media\Processing;

use RuntimeException;

/**
 * Writes already-produced derivative bytes to a resolved local derivative path.
 *
 * Image engines remain optional. This writer can be used by future GD/Imagick
 * processor packages once they have generated derivative bytes.
 */
final readonly class LocalMediaDerivativeWriter
{
    public function write(LocalMediaDerivativePath $path, string $contents): void
    {
        if ($contents === '') {
            throw new RuntimeException('Derivative contents cannot be empty.');
        }

        $directory = dirname($path->absolutePath);
        if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
            throw new RuntimeException('Unable to create derivative directory: ' . $directory);
        }

        if (file_put_contents($path->absolutePath, $contents) === false) {
            throw new RuntimeException('Unable to write media derivative: ' . $path->absolutePath);
        }
    }
}
