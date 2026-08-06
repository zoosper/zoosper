<?php

declare(strict_types=1);

it('applies the page-shell contract to both canonical layout engines', function (): void {
    $root = dirname(__DIR__, 5);
    foreach (['layout.php', 'layout.latte'] as $layout) {
        $template = file_get_contents($root . '/themes/default/templates/' . $layout);
        expect($template)->toContain('<main class="site-main page-shell">')
            ->not->toContain('<main class="site-main">');
    }
});

it('keeps the page-shell visual contract in the default theme stylesheet', function (): void {
    $root = dirname(__DIR__, 5);
    $styles = [];
    foreach (glob($root . '/themes/*/assets/css/*.css') ?: [] as $file) {
        $contents = file_get_contents($file);
        if (str_contains($contents, '.page-shell')) {
            $styles[] = $contents;
        }
    }

    expect($styles)->not->toBeEmpty()
        ->and(implode("\n", $styles))->toContain('.page-shell')
        ->toContain('max-width')
        ->toContain('margin');
});
