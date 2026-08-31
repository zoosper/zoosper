<?php

declare(strict_types=1);

it('declares and ships contract-safe Permission Explorer assets', function (): void {
    $module = dirname(__DIR__, 2);
    $assets = require $module . '/config/admin_assets.php';
    $runtime = file_get_contents($module . '/resources/assets/admin/js/permission-explorer.js');
    expect($assets)->toHaveKeys(['zoosper-permission-explorer-style', 'zoosper-permission-explorer-runtime'])
        ->and($runtime)->toContain('permission_ids[]')->toContain('Expand all')->toContain('Collapse all')->toContain("event.key === 'Escape'")->toContain('!checkbox.disabled')->not->toContain('fetch(')->not->toContain('.submit(');
});


it('keeps the Permission Explorer theme-aware responsive and source-aligned', function (): void {
    $module = dirname(__DIR__, 2);
    $css = (string) file_get_contents($module . '/resources/assets/admin/css/permission-explorer.css');
    $publicCss = (string) file_get_contents(dirname($module, 2) . '/public/assets/admin/css/permission-explorer.css');

    expect($publicCss)->toBe($css)
        ->and($css)->toContain('var(--admin-surface')
        ->toContain('var(--admin-border')
        ->toContain(':root[data-admin-theme="dark"] .permission-explorer')
        ->toContain('@media (max-width: 24.375rem)')
        ->toContain('.permission-explorer__count { justify-self: end;')
        ->toContain('.admin-content .permission-explorer :is(.permission-explorer__button, .permission-explorer__group-toggle)')
        ->not->toContain('background:#fff')
        ->not->toMatch('/\son[a-z]+\s*=/i')
        ->not->toContain('<script');
});










