<?php

declare(strict_types=1);

namespace Zoosper\Core\Tests\Unit\Module;

use RuntimeException;

/** @return list<string> */
function firstPartyComposerManifests(string $basePath): array
{
    $manifests = array_merge(
        glob($basePath . '/app/*/composer.json') ?: [],
        glob($basePath . '/packages/*/composer.json') ?: [],
    );
    sort($manifests);

    return $manifests;
}

test('every first-party Composer package declares Marko module identity', function (): void {
    $basePath = dirname(__DIR__, 5);
    $manifests = firstPartyComposerManifests($basePath);

    expect($manifests)->not->toBeEmpty();

    foreach ($manifests as $manifest) {
        $data = json_decode((string) file_get_contents($manifest), true, flags: JSON_THROW_ON_ERROR);

        if (!is_array($data)) {
            throw new RuntimeException('Invalid Composer manifest: ' . $manifest);
        }

        expect($data['extra']['marko']['module'] ?? null)
            ->toBeTrue('Missing Marko module identity in ' . $manifest);
    }
});
