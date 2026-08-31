<?php

declare(strict_types=1);

namespace Zoosper\Core\Tests\Unit\Architecture;

/** @return list<string> */
function phpFilesBelow(string $path): array
{
    if (!is_dir($path)) {
        return [];
    }

    $files = [];
    $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path));

    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $files[] = $file->getPathname();
        }
    }

    sort($files);

    return $files;
}

test('API and core source do not depend on the Latte implementation', function (): void {
    $basePath = dirname(__DIR__, 5);
    $sourceRoots = [
        $basePath . '/app/zoosper-api/src',
        $basePath . '/app/zoosper-core/src',
    ];
    $scanned = 0;

    foreach ($sourceRoots as $sourceRoot) {
        foreach (phpFilesBelow($sourceRoot) as $file) {
            $source = (string) file_get_contents($file);
            ++$scanned;

            expect($source)->not->toContain(
                'Latte\\',
                'API/core source must not import Latte directly: ' . $file,
            );
            expect($source)->not->toContain(
                'LatteTemplateEngine',
                'API/core source must not depend on Zoosper\'s default engine: ' . $file,
            );
        }
    }

    expect($scanned)->toBeGreaterThan(0);
});

test('the theme module owns the default Latte binding behind the engine contract', function (): void {
    $basePath = dirname(__DIR__, 5);
    $services = (string) file_get_contents($basePath . '/app/zoosper-theme/config/services.php');
    $contract = (string) file_get_contents(
        $basePath . '/app/zoosper-theme/src/Template/Engine/TemplateEngineInterface.php',
    );

    expect($services)->toContain('TemplateEngineInterface::class');
    expect($services)->toContain('LatteTemplateEngine::class');
    expect($contract)->toContain('public function extensions(): array;');
    expect($contract)->toContain('public function renderFile(string $path, array $data): string;');
});










