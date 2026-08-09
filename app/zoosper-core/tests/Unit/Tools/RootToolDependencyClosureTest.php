<?php

declare(strict_types=1);

it('keeps the root gate and site lookup audit available', function (): void {
    $root = dirname(__DIR__, 5);

    expect($root . '/tools/gate.php')->toBeFile()
        ->and($root . '/tools/site-lookup.php')->toBeFile()
        ->and($root . '/config/durable-tools.php')->toBeFile()
        ->and($root . '/tools/README.md')->toBeFile();
});

it('keeps only a bounded live root tool dependency closure', function (): void {
    $root = dirname(__DIR__, 5);
    $files = array_values(array_filter(
        glob($root . '/tools/*') ?: [],
        static fn (string $file): bool => is_file($file)
            && in_array(pathinfo($file, PATHINFO_EXTENSION), ['php', 'sh'], true),
    ));

    expect(count($files))->toBeLessThanOrEqual(30);
    foreach (array_map('basename', $files) as $basename) {
        expect($basename)->not->toMatch('/phase[-_]?\d/i');
    }
});
