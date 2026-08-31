<?php

declare(strict_types=1);

namespace Zoosper\Core\Module;

/**
 * Centralises Composer package classification for runtime module discovery.
 *
 * Zoosper exposes only `type: zoosper-module` as its public authoring contract.
 * Marko package recognition is private compatibility used to compose with
 * upstream modules without requiring Zoosper-owned forks.
 */
final class ComposerPackageModuleClassifier
{
    /** @param array<string, mixed> $package */
    public static function isRuntimeModule(array $package): bool
    {
        $type = $package['type'] ?? null;
        if ($type === 'zoosper-module' || $type === 'marko-module') {
            return true;
        }

        $extra = is_array($package['extra'] ?? null) ? $package['extra'] : [];
        $marko = is_array($extra['marko'] ?? null) ? $extra['marko'] : [];

        return ($marko['module'] ?? false) === true;
    }
}










