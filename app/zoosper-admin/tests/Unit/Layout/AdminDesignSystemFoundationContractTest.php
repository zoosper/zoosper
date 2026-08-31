<?php

declare(strict_types=1);

it('owns the Fable-informed semantic Admin design foundation without a competing token layer', function (): void {
    $root = dirname(__DIR__, 5);
    $shell = (string) file_get_contents($root . '/app/zoosper-admin/resources/assets/css/admin-shell.css');
    $components = (string) file_get_contents($root . '/app/zoosper-admin/resources/assets/css/admin-components.css');

    expect($shell)
        ->toContain('/* Fable-informed semantic foundation. Admin remains the single token owner. */')
        ->toContain('--admin-brand-50: #eafaf4;')
        ->toContain('--admin-brand-100: #d1f4e6;')
        ->toContain('--admin-brand-500: #12a67a;')
        ->toContain('--admin-brand-600: #0d8a65;')
        ->toContain('--admin-brand-700: #0a6e51;')
        ->toContain('--admin-bg: #f4f6f8;')
        ->toContain('--admin-surface: #ffffff;')
        ->toContain('--admin-surface-sunken: #eef1f4;')
        ->toContain('--admin-text-strong: #0f1720;')
        ->toContain('--admin-text: #33414f;')
        ->toContain('--admin-text-muted: #5c6b7a;')
        ->toContain('--admin-text-faint: #626e7b;')
        ->toContain('--admin-border: #dde3e9;')
        ->toContain('--admin-border-strong: #c6ced7;')
        ->toContain('--admin-space-1: .25rem;')
        ->toContain('--admin-space-10: 2.5rem;')
        ->toContain('--admin-radius-sm: .375rem;')
        ->toContain('--admin-radius-md: .625rem;')
        ->toContain('--admin-radius-lg: .875rem;')
        ->toContain('--admin-radius-pill: 999px;')
        ->toContain('--admin-shadow-xs:')
        ->toContain('--admin-shadow-md:')
        ->toContain(':root[data-admin-theme="dark"]')
        ->toContain(':root[data-admin-theme-palette="ocean"]')
        ->not->toContain('<style')
        ->and($components)
        ->toContain('--admin-warning: #845206;')
        ->toContain('--admin-control-radius: var(--admin-radius-md);')
        ->toContain('background: var(--admin-brand-700);')
        ->toContain('border-radius: var(--admin-radius-pill);')
        ->not->toMatch('/javascript\s*:/i');
});

it('keeps the design foundation fluid and free from prototype-only navigation counts', function (): void {
    $root = dirname(__DIR__, 5);
    $shell = (string) file_get_contents($root . '/app/zoosper-admin/resources/assets/css/admin-shell.css');
    $layout = (string) file_get_contents($root . '/themes/admin/default/templates/layout.php');
    $navigation = (string) file_get_contents($root . '/app/zoosper-admin/src/Navigation/AdminNavigationRenderer.php');

    expect($shell)->toContain('max-width: none;')
        ->toContain('minmax(0, 1fr)')
        ->not->toContain('1440px')
        ->not->toContain('1600px')
        ->and($layout . $navigation)
        ->not->toContain('data-navigation-count')
        ->not->toContain('admin-nav-badge');
});










