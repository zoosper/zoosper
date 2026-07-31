<?php

declare(strict_types=1);

namespace Zoosper\Core\Tests\Unit\Module;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

test('first-party runtime module names match Composer package identities', function (): void {
    $basePath = dirname(__DIR__, 5);

    foreach (['app', 'packages'] as $directory) {
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($basePath . '/' . $directory)) as $file) {
            if (!$file->isFile() || $file->getFilename() !== 'composer.json') {
                continue;
            }

            $packageRoot = $file->getPath();
            $moduleFile = $packageRoot . '/module.php';
            if (!is_file($moduleFile)) {
                continue;
            }

            $composer = json_decode((string) file_get_contents($file->getPathname()), true, flags: JSON_THROW_ON_ERROR);
            $module = require $moduleFile;
            $expectedName = str_replace('/', '-', (string) ($composer['name'] ?? ''));

            expect($module)->toBeArray();
            expect($module['name'] ?? null)
                ->toBe($expectedName, 'Runtime/package identity mismatch in ' . $packageRoot);
        }
    }
});
