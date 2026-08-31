<?php

declare(strict_types=1);

namespace Zoosper\Core\Tests\Unit\Composer;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/** @return list<string> */
function zoosperComposerManifests(string $basePath): array
{
    $manifests = [$basePath . '/composer.json'];

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

test('first-party dependencies never use unbounded development constraints', function (): void {
    $basePath = dirname(__DIR__, 5);

    foreach (zoosperComposerManifests($basePath) as $manifest) {
        $data = json_decode((string) file_get_contents($manifest), true, flags: JSON_THROW_ON_ERROR);

        foreach (['require', 'require-dev'] as $section) {
            foreach ($data[$section] ?? [] as $package => $constraint) {
                if (!str_starts_with((string) $package, 'zoosper/')) {
                    continue;
                }

                expect($constraint)
                    ->not->toBe('*@dev', 'Unbounded constraint in ' . $manifest . ': ' . $package)
                    ->not->toBe('*', 'Unbounded constraint in ' . $manifest . ': ' . $package)
                    ->toBe('dev-dev', 'Unexpected pre-release constraint in ' . $manifest . ': ' . $package);
            }
        }
    }
});










