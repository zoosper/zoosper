<?php

declare(strict_types=1);


it('keeps current canonical documentation aligned with shipped Page and Media capabilities', function (): void {
    $root = dirname(__DIR__, 5);
    $docs = $root . '/docs';
    $content = '';

    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($docs));
    foreach ($iterator as $file) {
        if (!$file->isFile() || $file->getExtension() !== 'md') {
            continue;
        }
        if (str_contains($file->getPathname(), '/docs/releases/v')) {
            continue;
        }
        $content .= "\n" . (string) file_get_contents($file->getPathname());
    }

    expect($content)
        ->not->toContain('Page revision history is under active 0.2 development.')
        ->not->toContain('Derivative processing remains disabled')
        ->not->toContain('The 0.3.1 line is a deliberate minor public-alpha progression')
        ->toContain('Page revision history, preview, pagination, and restoration are available')
        ->toContain('WebP derivative processing is available')
        ->toContain('0.3.2-alpha.1-dev')
        ->toContain('v0.3.1-alpha.1');
});

it('builds the public documentation site without stale current claims', function (): void {
    $root = dirname(__DIR__, 5);
    $build = $root . '/docs-site/build';

    expect($build . '/index.html')->toBeFile()
        ->and($build . '/user-guide/index.html')->toBeFile()
        ->and($build . '/releases/current-development/index.html')->toBeFile();

    $content = (string) file_get_contents($build . '/user-guide/index.html')
        . "\n" . (string) file_get_contents($build . '/releases/current-development/index.html');

    expect($content)
        ->not->toContain('Page revision history is under active 0.2 development.')
        ->not->toContain('Derivative processing remains disabled')
        ->not->toContain('The 0.3.1 line is a deliberate minor public-alpha progression')
        ->toContain('0.3.2-alpha.1-dev');
});
