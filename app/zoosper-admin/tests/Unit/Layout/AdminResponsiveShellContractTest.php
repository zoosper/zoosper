<?php

declare(strict_types=1);

it('keeps the Admin shell responsive, accessible and free from inline behaviour', function (): void {
    $root = dirname(__DIR__, 5);
    $layout = (string) file_get_contents($root . '/themes/admin/default/templates/layout.php');

    expect($layout)
        ->toContain('class="admin-skip-link" href="#admin-content"')
        ->toContain('data-admin-shell')
        ->toContain('id="admin-navigation"')
        ->toContain('data-admin-navigation-toggle')
        ->toContain('aria-controls="admin-navigation"')
        ->toContain('aria-expanded="false"')
        ->toContain('data-admin-sidebar-toggle')
        ->toContain('data-admin-theme-toggle')
        ->toContain('id="admin-content" tabindex="-1"')
        ->toContain('/assets/brand/logo.svg')
        ->toContain('/assets/brand/favicon.svg')
        ->not->toMatch('/\son[a-z]+\s*=/i')
        ->not->toMatch('/\sstyle\s*=/i')
        ->not->toContain('<script');
});

it('registers the CSP-safe Admin-owned shell assets in deterministic order', function (): void {
    $root = dirname(__DIR__, 5);
    $manifest = require $root . '/app/zoosper-admin/config/admin_assets.php';
    $assets = $manifest['assets'];

    expect(is_file($root . '/app/zoosper-admin/resources/assets/css/admin-shell.css'))->toBeTrue()
        ->and(is_file($root . '/app/zoosper-admin/resources/assets/js/admin-shell.js'))->toBeTrue()
        ->and($assets['zoosper-admin-shell-style'])
        ->toMatchArray([
            'type' => 'style',
            'path' => '/asset/zoosper-admin/css/admin-shell.css?v=1.37l',
            'sort_order' => 15,
        ])
        ->and($assets['zoosper-admin-shell-script'])
        ->toMatchArray([
            'type' => 'script',
            'path' => '/asset/zoosper-admin/js/admin-shell.js?v=1.37l',
            'sort_order' => 10,
            'defer' => true,
        ]);

    expect($assets['zoosper-admin-base']['sort_order'])
        ->toBeLessThan($assets['zoosper-admin-shell-style']['sort_order'])
        ->and($assets['zoosper-admin-shell-style']['sort_order'])
        ->toBeLessThan($assets['zoosper-admin-messages-style']['sort_order']);
});

it('provides theme, responsive, keyboard and reduced-motion shell contracts', function (): void {
    $root = dirname(__DIR__, 5);
    $css = (string) file_get_contents($root . '/app/zoosper-admin/resources/assets/css/admin-shell.css');
    $script = (string) file_get_contents($root . '/app/zoosper-admin/resources/assets/js/admin-shell.js');

    expect($css)
        ->toContain(':root[data-admin-theme="dark"]')
        ->toContain('grid-template-columns: var(--admin-sidebar-width) minmax(0, 1fr)')
        ->toContain('@media (max-width: 860px)')
        ->toContain('@media (prefers-reduced-motion: reduce)')
        ->toContain(':focus-visible')
        ->and($script)
        ->toContain("window.matchMedia('(prefers-color-scheme: dark)')")
        ->toContain("window.matchMedia('(max-width: 860px)')")
        ->toContain("event.key === 'Escape'")
        ->toContain("event.key !== 'Tab'")
        ->toContain('navigationToggle.focus()')
        ->toContain('textContent =')
        ->not->toContain('innerHTML')
        ->not->toContain('document.write')
        ->not->toContain('eval(');
});
