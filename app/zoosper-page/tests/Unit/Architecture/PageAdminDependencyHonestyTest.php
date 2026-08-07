<?php

declare(strict_types=1);

it('declares the remaining Admin dependency honestly and limits Page to approved shared contracts', function (): void {
    $root = dirname(__DIR__, 5);
    $composer = json_decode(
        (string) file_get_contents($root . '/app/zoosper-page/composer.json'),
        true,
        512,
        JSON_THROW_ON_ERROR,
    );
    expect($composer['require'])->toHaveKey('zoosper/admin', 'dev-dev');

    $approved = [
        'Zoosper\\Admin\\Editor\\ContentEditorInterface',
        'Zoosper\\Admin\\Form\\AdminFormConfigAggregator',
        'Zoosper\\Admin\\Message\\FlashMessageStoreInterface',
    ];
    $unexpected = [];
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(
        $root . '/app/zoosper-page', FilesystemIterator::SKIP_DOTS,
    ));
    foreach ($iterator as $file) {
        if ($file->getExtension() !== 'php' || str_contains($file->getPathname(), '/tests/')) {
            continue;
        }
        $source = (string) file_get_contents($file->getPathname());
        preg_match_all('/^use (Zoosper\\\\Admin\\\\[^;]+);/m', $source, $matches);
        foreach ($matches[1] as $import) {
            if (!in_array($import, $approved, true)) {
                $unexpected[] = $file->getPathname() . ': ' . $import;
            }
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
