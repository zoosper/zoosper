<?php

declare(strict_types=1);

it('removes the Page dependency on Admin after shared contracts migrate to Core', function (): void {
    $root = dirname(__DIR__, 5);
    $composer = json_decode(
        (string) file_get_contents($root . '/app/zoosper-page/composer.json'),
        true,
        512,
        JSON_THROW_ON_ERROR,
    );
    expect($composer['require'])->not->toHaveKey('zoosper/admin');

    $unexpected = [];
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(
        $root . '/app/zoosper-page', FilesystemIterator::SKIP_DOTS,
    ));
    foreach ($iterator as $file) {
        $path = str_replace('\\', '/', $file->getPathname());
        if ($file->getExtension() !== 'php' || str_contains($path, '/tests/')) {
            continue;
        }
        $source = (string) file_get_contents($file->getPathname());
        if (str_contains($source, 'use Zoosper' . chr(92) . 'Admin' . chr(92))) {
            $unexpected[] = $path;
        }
    }
    expect($unexpected)->toBe([]);
});

it('keeps concrete Admin editor and UI implementations out of Page runtime', function (): void {
    $root = dirname(__DIR__, 5);
    $source = '';
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(
        $root . '/app/zoosper-page/src', FilesystemIterator::SKIP_DOTS,
    ));
    foreach ($iterator as $file) {
        if ($file->getExtension() === 'php') {
            $source .= (string) file_get_contents($file->getPathname());
        }
    }
    expect($source)->not->toContain('EditorJsContentEditor')
        ->not->toContain('TextareaContentEditor')
        ->not->toContain('Zoosper\\Admin\\Layout\\AdminLayout')
        ->not->toContain('Zoosper\\Admin\\UI\\AdminViewRenderer');
});
