<?php

declare(strict_types=1);

it('requires one meaningful root README for every first-party Composer package', function (): void {
    $root = dirname(__DIR__, 5);
    $composerFiles = array_merge(
        glob($root . '/app/*/composer.json') ?: [],
        glob($root . '/packages/*/composer.json') ?: [],
    );

    expect($composerFiles)->not->toBeEmpty();

    foreach ($composerFiles as $composerFile) {
        $package = dirname($composerFile);
        $readme = $package . '/README.md';
        expect($readme)->toBeFile();

        $content = (string) file_get_contents($readme);
        expect(strlen($content))->toBeGreaterThan(500)
            ->and($content)->toContain('## Responsibilities')
            ->toContain('## Dependencies')
            ->toContain('## Testing')
            ->toContain('## Operational notes')
            ->toContain('zcomposer test')
            ->toContain('php8.5 tools/gate.php');
    }
});

it('allows only package root READMEs plus the maintained schema reference', function (): void {
    $root = dirname(__DIR__, 5);
    $allowed = [$root . '/packages/zoosper-database/src/Schema/README.md'];
    foreach (array_merge(glob($root . '/app/*/composer.json') ?: [], glob($root . '/packages/*/composer.json') ?: []) as $composerFile) {
        $allowed[] = dirname($composerFile) . '/README.md';
    }

    $actual = [];
    foreach (['app', 'packages'] as $base) {
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root . '/' . $base, FilesystemIterator::SKIP_DOTS));
        foreach ($iterator as $file) {
            if ($file->isFile() && strtolower($file->getBasename()) === 'readme.md') {
                $actual[] = $file->getPathname();
            }
        }
    }

    $allowed = array_map(static fn(string $p): string => str_replace('\\', '/', $p), $allowed);
    $actual = array_map(static fn(string $p): string => str_replace('\\', '/', $p), $actual);
    sort($allowed);
    sort($actual);
    expect($actual)->toBe($allowed);
});

it('does not retain patch notes or historical package documentation trees', function (): void {
    $root = dirname(__DIR__, 5);
    expect(glob($root . '/app/**/*.patch*.md'))->toBe([])
        ->and($root . '/app/zoosper-admin/docs/launch-readiness-stubs')->not->toBeDirectory()
        ->and($root . '/packages/zoosper-media/docs')->not->toBeDirectory();
});










