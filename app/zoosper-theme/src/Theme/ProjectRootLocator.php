<?php

declare(strict_types=1);

namespace Zoosper\Theme\Theme;

use RuntimeException;

/** Resolves the owning zoosper/zoosper project independently of module installation layout. */
final class ProjectRootLocator
{
    public static function from(string $path): string
    {
        $candidate = is_dir($path) ? $path : dirname($path);

        while (true) {
            $composerFile = $candidate . '/composer.json';
            if (is_file($composerFile)) {
                $composer = json_decode((string) file_get_contents($composerFile), true);
                if (is_array($composer) && ($composer['name'] ?? null) === 'zoosper/zoosper') {
                    return $candidate;
                }
            }

            $parent = dirname($candidate);
            if ($parent === $candidate) {
                throw new RuntimeException('Theme runtime cannot locate the zoosper/zoosper project root.');
            }
            $candidate = $parent;
        }
    }
}
