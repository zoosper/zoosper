<?php

declare(strict_types=1);

it('keeps pagination ownership outside Core and Marko behind the Zoosper package', function (): void {
    $root = dirname(__DIR__, 4);
    expect($root . '/app/zoosper-core/src/Pagination')->not->toBeDirectory();

    $legacy = 'Zoosper\Core\' . 'Pagination';
    $marko = 'Marko\' . 'Pagination';
    foreach (['app', 'packages', 'themes'] as $base) {
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root . '/' . $base, FilesystemIterator::SKIP_DOTS));
        foreach ($iterator as $file) {
            if (!$file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }
            $relative = str_replace('\\', '/', substr($file->getPathname(), strlen($root) + 1));
            $source = (string) file_get_contents($file->getPathname());
            expect($source)->not->toContain($legacy, 'Legacy Core pagination import: ' . $relative);
            if (str_contains($source, $marko)) {
                expect($relative)->toStartWith('packages/zoosper-pagination/', 'Marko pagination import escaped ownership: ' . $relative);
            }
        }
    }
});

it('requires the Zoosper boundary directly from Grid', function (): void {
    $root = dirname(__DIR__, 4);
    $grid = json_decode((string) file_get_contents($root . '/packages/zoosper-grid/composer.json'), true, flags: JSON_THROW_ON_ERROR);
    $pagination = json_decode((string) file_get_contents($root . '/packages/zoosper-pagination/composer.json'), true, flags: JSON_THROW_ON_ERROR);

    expect($grid['require']['zoosper/pagination'] ?? null)->toBe('dev-dev')
        ->and($grid['require'])->not->toHaveKey('zoosper/core')
        ->and($pagination['require']['marko/pagination'] ?? null)->toBe('0.8.5');
});











