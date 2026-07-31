<?php

declare(strict_types=1);

namespace Zoosper\Core\Tests\Unit\Module;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

test('first-party module files do not duplicate Composer package versions', function (): void {
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

            $module = require $moduleFile;

            expect($module)->toBeArray();
            expect($module)->not->toHaveKey(
                'version',
                'Composer owns package version; remove duplicate version from ' . $moduleFile,
            );
        }
    }
});
