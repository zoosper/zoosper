<?php

declare(strict_types=1);

namespace Zoosper\Core\Tests\Unit\Module;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

test('first-party module files do not duplicate Composer package identity', function (): void {
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

            expect($composer['name'] ?? null)->toBeString()->not->toBe('');
            expect($composer['extra']['marko']['module'] ?? null)->toBeTrue();
            expect($module)->toBeArray();
            expect($module)->not->toHaveKey(
                'name',
                'Composer owns package identity; remove duplicate name from ' . $moduleFile,
            );
        }
    }
});
