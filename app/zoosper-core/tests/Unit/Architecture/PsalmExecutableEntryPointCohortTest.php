<?php

declare(strict_types=1);

it('keeps declarative executable Psalm APIs backed by runtime configuration', function (): void {
    $root = dirname(__DIR__, 5);
    $configTexts = [];
    foreach ([$root . '/app', $root . '/packages', $root . '/config'] as $configRoot) {
        if (!is_dir($configRoot)) {
            continue;
        }
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($configRoot));
        foreach ($iterator as $file) {
            $path = $file->getPathname();
            if (!$file->isFile() || $file->getExtension() !== 'php' || str_contains($path, '/tests/') || str_contains($path, '/src/')) {
                continue;
            }
            $configTexts[] = (string) file_get_contents($path);
        }
    }

    $matched = 0;
    foreach ([$root . '/app', $root . '/packages'] as $sourceRoot) {
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($sourceRoot));
        foreach ($iterator as $file) {
            $path = $file->getPathname();
            if (!$file->isFile() || $file->getExtension() !== 'php' || !str_contains($path, '/src/')) {
                continue;
            }
            $source = (string) file_get_contents($path);
            if (!str_contains($source, 'Declarative runtime entry point discovered through module configuration.')) {
                continue;
            }
            $class = basename($path, '.php');
            $registered = array_filter($configTexts, static fn (string $text): bool => str_contains($text, $class));
            expect($source)->toContain('@psalm-api')
                ->and($registered)->not->toBeEmpty("No runtime configuration evidence for {$path}");
            ++$matched;
        }
    }
    expect($matched)->toBe(77);
});
