<?php

declare(strict_types=1);

namespace Zoosper\Core\Tests\Unit\Module;

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

test('every first-party runtime module declares Marko module identity', function (): void {
    foreach (firstPartyManifestPaths() as $manifestPath) {
        $manifest = json_decode(
            (string) file_get_contents($manifestPath),
            true,
            flags: JSON_THROW_ON_ERROR,
        );

        if (($manifest['type'] ?? null) !== 'zoosper-module') {
            continue;
        }

        expect($manifest['extra']['marko']['module'] ?? null)
            ->toBeTrue('Missing Marko module identity in ' . $manifestPath);
    }
});

test('first-party library packages do not claim runtime module identity', function (): void {
    foreach (firstPartyManifestPaths() as $manifestPath) {
        $manifest = json_decode(
            (string) file_get_contents($manifestPath),
            true,
            flags: JSON_THROW_ON_ERROR,
        );

        if (($manifest['type'] ?? null) !== 'library') {
            continue;
        }

        expect($manifest['extra']['marko']['module'] ?? false)
            ->toBeFalse('Library incorrectly claims runtime module identity: ' . $manifestPath);
    }
});
