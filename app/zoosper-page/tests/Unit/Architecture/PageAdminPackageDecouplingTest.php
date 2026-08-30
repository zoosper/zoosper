<?php

declare(strict_types=1);

it('keeps Page free of Admin imports and Composer dependency', function (): void {
    $root = dirname(__DIR__, 5);
    $composer = json_decode((string) file_get_contents($root . '/app/zoosper-page/composer.json'), true, 512, JSON_THROW_ON_ERROR);
    expect($composer['require'])->not->toHaveKey('zoosper/admin');
    $source = '';
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root . '/app/zoosper-page', FilesystemIterator::SKIP_DOTS));
    foreach ($iterator as $file) {
        $path = str_replace('\\', '/', $file->getPathname());
        if ($file->getExtension() === 'php' && !str_contains($path, '/tests/')) {
            $source .= (string) file_get_contents($file->getPathname());
        }
    }
    expect($source)->not->toContain('use Zoosper\\Admin\\')
        ->toContain('Zoosper\\Core\\Editor\\ContentEditorInterface')
        ->toContain('Zoosper\\Core\\Message\\FlashMessageStoreInterface')
        ->toContain('Zoosper\\Core\\Form\\AdminFormConfigAggregator');
});
