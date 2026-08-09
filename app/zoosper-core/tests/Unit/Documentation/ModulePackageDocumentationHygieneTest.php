<?php

declare(strict_types=1);

it('keeps only concise current module and package readmes', function (): void {
    $root = dirname(__DIR__, 5);
    $expected = [
        'app/zoosper-core/src/Schema/README.md',
        'app/zoosper-page/README.md',
        'app/zoosper-settings/README.md',
        'packages/zoosper-admin-grid/README.md',
        'packages/zoosper-errors/README.md',
        'packages/zoosper-grid/README.md',
        'packages/zoosper-media/README.md',
    ];

    $actual = [];
    foreach (['app', 'packages'] as $base) {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($root . '/' . $base, FilesystemIterator::SKIP_DOTS),
        );
        foreach ($iterator as $file) {
            if ($file->isFile() && strtolower($file->getExtension()) === 'md') {
                $actual[] = str_replace($root . '/', '', $file->getPathname());
            }
        }
    }

    sort($actual);
    sort($expected);
    expect($actual)->toBe($expected);
});

it('does not retain patch notes or historical package documentation trees', function (): void {
    $root = dirname(__DIR__, 5);
    expect(glob($root . '/app/**/*.patch*.md'))->toBe([])
        ->and($root . '/app/zoosper-admin/docs/launch-readiness-stubs')->not->toBeDirectory()
        ->and($root . '/packages/zoosper-media/docs')->not->toBeDirectory();
});
