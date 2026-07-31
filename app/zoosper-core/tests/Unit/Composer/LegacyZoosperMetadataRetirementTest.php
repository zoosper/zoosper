<?php

declare(strict_types=1);

namespace Zoosper\Core\Tests\Unit\Composer;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

test('first-party packages use Marko identity without legacy Zoosper metadata', function (): void {
    $basePath = dirname(__DIR__, 5);

    foreach (['app', 'packages'] as $directory) {
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($basePath . '/' . $directory)) as $file) {
            if (!$file->isFile() || $file->getFilename() !== 'composer.json') {
                continue;
            }

            $manifest = json_decode((string) file_get_contents($file->getPathname()), true, flags: JSON_THROW_ON_ERROR);

            expect($manifest['extra']['marko']['module'] ?? null)
                ->toBeTrue('Missing Marko identity in ' . $file->getPathname());
            expect($manifest['extra'] ?? [])
                ->not->toHaveKey('zoosper', 'Legacy metadata remains in ' . $file->getPathname());
        }
    }
});
