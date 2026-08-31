<?php

declare(strict_types=1);

namespace Zoosper\Core\Tests\Unit\Architecture;

test('generic grid implementation is owned by the zoosper grid package', function (): void {
    $basePath = dirname(__DIR__, 5);

    expect($basePath . '/packages/zoosper-grid/composer.json')->toBeFile();
    expect($basePath . '/packages/zoosper-grid/src/GridDefinition.php')->toBeFile();
    expect($basePath . '/app/zoosper-core/src/Grid')->not->toBeDirectory();
});

test('grid consumers use the package namespace rather than the retired core namespace', function (): void {
    $basePath = dirname(__DIR__, 5);
    foreach (['app/zoosper-admin', 'app/zoosper-page', 'packages/zoosper-grid'] as $relativeRoot) {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($basePath . '/' . $relativeRoot),
        );
        foreach ($iterator as $file) {
            $path = str_replace('\\', '/', $file->getPathname());
            if ($file->isFile() && $file->getExtension() === 'php' && !str_contains($path, '/tests/')) {
                expect((string) file_get_contents($file->getPathname()))
                    ->not->toContain('Zoosper\Core\Grid\\');
            }
        }
    }
});










