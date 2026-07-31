<?php

declare(strict_types=1);

namespace Zoosper\Core\Tests\Unit\Module;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;

/** @return list<string> */
function firstPartyComposerManifests(string $basePath): array
{
    $manifests = [];

    foreach (['app', 'packages'] as $directory) {
        $root = $basePath . '/' . $directory;
        if (!is_dir($root)) {
            continue;
        }

        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root)) as $file) {
            if ($file->isFile() && $file->getFilename() === 'composer.json') {
                $manifests[] = $file->getPathname();
            }
        }
    }

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
