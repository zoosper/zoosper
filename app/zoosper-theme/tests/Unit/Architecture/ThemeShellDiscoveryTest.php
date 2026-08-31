<?php

declare(strict_types=1);

it('has exactly one canonical site-main page-shell layout boundary', function (): void {
    $root = dirname(__DIR__, 5);
    $matches = [];
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root . '/themes'));
    foreach ($iterator as $file) {
        if (!$file->isFile() || !in_array($file->getExtension(), ['php', 'latte', 'html'], true)) {
            continue;
        }
        if (str_contains(file_get_contents($file->getPathname()), '<main class="site-main page-shell">')) {
            $matches[] = $file->getPathname();
        }
    }

    expect($matches)->toHaveCount(2)
        ->and(implode(' ', $matches))->toContain('layout.php')->toContain('layout.latte');
});










