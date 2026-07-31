<?php

declare(strict_types=1);

namespace Zoosper\Core\Tests\Unit\Composer;

/** @return list<string> */
function firstPartyPackageManifests(string $basePath): array
{
    $manifests = array_merge(
        glob($basePath . '/app/*/composer.json') ?: [],
        glob($basePath . '/packages/*/composer.json') ?: [],
    );
    sort($manifests);

    return $manifests;
}

test('first-party packages use Marko identity without legacy Zoosper metadata', function (): void {
    $basePath = dirname(__DIR__, 5);
    $manifests = firstPartyPackageManifests($basePath);

    expect($manifests)->not->toBeEmpty();

    foreach ($manifests as $manifestPath) {
        $manifest = json_decode(
            (string) file_get_contents($manifestPath),
            true,
            flags: JSON_THROW_ON_ERROR,
        );

        expect($manifest['extra']['marko']['module'] ?? null)
            ->toBeTrue('Missing Marko identity in ' . $manifestPath);
        expect($manifest['extra'] ?? [])
            ->not->toHaveKey('zoosper', 'Legacy metadata remains in ' . $manifestPath);
    }
});
