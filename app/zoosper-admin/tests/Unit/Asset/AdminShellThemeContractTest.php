<?php

declare(strict_types=1);

it('keeps the complete Admin shell aligned with the selected colour palette and base mode', function (): void {
    $root = dirname(__DIR__, 5);
    $stylesheet = $root . '/app/zoosper-admin/resources/assets/css/admin-shell.css';
    $css = (string) file_get_contents($stylesheet);
    $manifest = require $root . '/app/zoosper-admin/config/admin_assets.php';
    $asset = $manifest['assets']['zoosper-admin-shell-style'] ?? null;
    $version = substr(hash('sha256', (string) preg_replace('~\r\n?~', "\n", $css)), 0, 12);

    expect($css)->toContain(':root {')
        ->toContain(':root[data-admin-theme="dark"]')
        ->toContain(':root[data-admin-theme-palette="ocean"]')
        ->toContain('--admin-sidebar: #ffffff;')
        ->toContain('--admin-sidebar: #080c12;')
        ->toContain('--admin-sidebar-border:')
        ->toContain('--admin-sidebar-hover:')
        ->toContain('--admin-sidebar-active:')
        ->toContain('--admin-sidebar-active-text:')
        ->toContain('--admin-sidebar-divider:')
        ->toContain('--admin-sidebar-scrollbar:')
        ->toContain('--admin-sidebar-brand:')
        ->toContain('background: var(--admin-sidebar-hover);')
        ->toContain('background: var(--admin-sidebar-active);')
        ->toContain('border-right: 1px solid var(--admin-sidebar-border);')
        ->toContain('@media (prefers-reduced-motion: reduce)')
        ->not->toContain('<style')
        ->not->toContain('javascript:')
        ->and($asset)->toBeArray()
        ->and($asset['type'] ?? null)->toBe('style')
        ->and($asset['path'] ?? null)->toBe('/asset/zoosper-admin/css/admin-shell.css?v=' . $version)
        ->and($asset['sort_order'] ?? null)->toBe(15);
});

it('retains the CSP-safe persisted theme control contract', function (): void {
    $root = dirname(__DIR__, 5);
    $layout = (string) file_get_contents($root . '/themes/admin/default/templates/layout.php');
    $runtime = (string) file_get_contents($root . '/app/zoosper-admin/resources/assets/js/admin-shell.js');

    expect($layout)->toContain('data-admin-theme-selector')
        ->toContain('data-admin-theme-mode=')
        ->toContain('Admin colour theme')
        ->not->toContain('onclick=')
        ->and($runtime)->toContain("const themeKey = 'zoosper.admin.theme';")
        ->toContain('root.dataset.adminTheme = mode;')
        ->toContain('root.dataset.adminThemePalette = palette;')
        ->toContain('themes.has(storedTheme)')
        ->toContain('window.localStorage.setItem(key, value)')
        ->not->toContain('innerHTML');
});










