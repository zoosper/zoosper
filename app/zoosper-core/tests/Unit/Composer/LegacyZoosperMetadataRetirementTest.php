<?php

declare(strict_types=1);

namespace Zoosper\Core\Tests\Unit\Composer;

/** @return list<string> */
function firstPartyManifestPaths(): array
{
    $basePath = dirname(__DIR__, 5);
    $paths = [];

    foreach (['app', 'packages'] as $directory) {
        $root = $basePath . '/' . $directory;
        if (!is_dir($root)) {
            continue;
        }
        foreach (glob($root . '/*/composer.json') ?: [] as $manifest) {
            $paths[] = $manifest;
        }
    }

    sort($paths);

    return $paths;
}

test('first-party packages use Marko identity without legacy Zoosper metadata', function (): void {
    foreach (firstPartyManifestPaths() as $manifestPath) {
        $manifest = json_decode(
            (string) file_get_contents($manifestPath),
            true,
            flags: JSON_THROW_ON_ERROR,
        );

        expect($manifest['extra'] ?? [])
            ->not->toHaveKey('zoosper', 'Legacy metadata remains in ' . $manifestPath);

        if (($manifest['type'] ?? null) !== 'zoosper-module') {
            continue;
        }

        expect($manifest['extra']['marko']['module'] ?? null)
            ->toBeTrue('Missing Marko identity in ' . $manifestPath);
    }
});
