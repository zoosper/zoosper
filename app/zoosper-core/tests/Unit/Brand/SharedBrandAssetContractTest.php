<?php

declare(strict_types=1);

it('publishes one canonical Zoosper mark to runtime brand paths', function (): void {
    $root = dirname(__DIR__, 5);
    $canonical = $root . '/app/zoosper-theme/resources/brand/mark.svg';
    expect($canonical)->toBeFile();
    foreach (['public/assets/brand/logo.svg', 'public/assets/brand/favicon.svg'] as $relative) {
        $published = $root . '/' . $relative;
        expect($published)->toBeFile()
            ->and(hash_file('sha256', $published))->toBe(hash_file('sha256', $canonical));
    }
});

it('uses shared branding in Admin frontend and documentation builds', function (): void {
    $root = dirname(__DIR__, 5);
    foreach ([
        'themes/admin/default/templates/layout.php',
        'themes/default/templates/layout.latte',
        'themes/default/templates/layout.php',
    ] as $relative) {
        $layout = (string) file_get_contents($root . '/' . $relative);
        expect($layout)->toContain('/assets/brand/logo.svg')
            ->toContain('/assets/brand/favicon.svg');
    }
    expect((string) file_get_contents($root . '/docs-site/build.php'))
        ->toContain("app/zoosper-theme/resources/brand/mark.svg");
});
