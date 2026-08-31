<?php

declare(strict_types=1);

namespace Zoosper\Core\Tests\Unit\Module;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

test('first-party package descriptions live in Composer manifests', function (): void {
    $basePath = dirname(__DIR__, 5);

    foreach (['app', 'packages'] as $directory) {
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($basePath . '/' . $directory)) as $file) {
            if (!$file->isFile() || $file->getFilename() !== 'composer.json') {
                continue;
            }

            $moduleFile = $file->getPath() . '/module.php';
            if (!is_file($moduleFile)) {
                continue;
            }

            $composer = json_decode((string) file_get_contents($file->getPathname()), true, flags: JSON_THROW_ON_ERROR);
            $module = require $moduleFile;

            expect($composer['description'] ?? null)
                ->toBeString()
                ->not->toBe('');
            expect($module)->toBeArray();
            expect($module)->not->toHaveKey(
                'description',
                'Composer owns package description; remove duplicate description from ' . $moduleFile,
            );
        }
    }
});










