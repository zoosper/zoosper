<?php
declare(strict_types=1);
test('keeps logging ownership outside Core Log and root owns only zoosper logger', function (): void {
    $root = dirname(__DIR__, 4);
    $composer = json_decode((string) file_get_contents($root . '/composer.json'), true, 512, JSON_THROW_ON_ERROR);
    $module = json_decode((string) file_get_contents($root . '/packages/zoosper-logger/composer.json'), true, 512, JSON_THROW_ON_ERROR);
    expect($root . '/app/zoosper-core/src/Log')->not->toBeDirectory()
        ->and($composer['require'])->toHaveKey('zoosper/logger', 'dev-dev')->not->toHaveKeys(['marko/log', 'marko/log-file'])
        ->and($module['require'])->toHaveKey('marko/log', '0.8.5')->toHaveKey('marko/log-file', '0.8.5');
    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root . '/app/zoosper-core/src', FilesystemIterator::SKIP_DOTS)) as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') expect((string) file_get_contents($file->getPathname()))->not->toContain('Zoosper\Core\Log');
    }
});











