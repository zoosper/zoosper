<?php

declare(strict_types=1);

it('uses Admin semantic tokens for Settings workspace surfaces in both themes', function (): void {
    $root = dirname(__DIR__, 5);
    $css = (string) file_get_contents(
        $root . '/app/zoosper-settings/resources/assets/css/settings-workspace.css',
    );

    expect($css)->toContain('--settings-surface: var(--admin-surface')
        ->toContain('--settings-surface-muted: var(--admin-surface-muted')
        ->toContain('--settings-border: var(--admin-border')
        ->toContain('--settings-text: var(--admin-text')
        ->toContain('--settings-text-muted: var(--admin-text-muted')
        ->toContain(':root[data-admin-theme="dark"] .settings-workspace')
        ->toContain('color-scheme: dark')
        ->toContain('.settings-more-actions-panel,')
        ->toContain('background: var(--settings-surface)')
        ->not->toContain('<style')
        ->not->toContain('javascript:');
});

it('bounds and compacts the desktop More Actions panel', function (): void {
    $root = dirname(__DIR__, 5);
    $css = (string) file_get_contents(
        $root . '/app/zoosper-settings/resources/assets/css/settings-workspace.css',
    );

    expect($css)->toContain('@media screen and (min-width:53.1875rem)')
        ->toContain('width:min(54rem,calc(100vw - 2rem))')
        ->toContain('max-height:min(66vh,34rem)')
        ->toContain('overflow-y:auto')
        ->toContain('grid-template-columns:repeat(2,minmax(0,1fr))')
        ->toContain('.settings-saved-view-label,')
        ->toContain('grid-column:1 / -1');
});

it('keeps the Settings workspace usable at the approved 390px viewport', function (): void {
    $root = dirname(__DIR__, 5);
    $css = (string) file_get_contents(
        $root . '/app/zoosper-settings/resources/assets/css/settings-workspace.css',
    );

    expect($css)->toContain('@media screen and (max-width:24.375rem)')
        ->toContain('.settings-toolbar-primary{display:grid;grid-template-columns:1fr 1fr;width:100%}')
        ->toContain('.settings-actions{align-items:stretch;flex-direction:column}')
        ->toContain('.settings-nav{gap:.25rem;padding:.4rem;scrollbar-width:thin}');
});


it('keeps normal Settings toolbar controls aligned', function (): void {
    $root = dirname(__DIR__, 3);
    $css = (string) file_get_contents($root . '/resources/assets/css/settings-workspace.css');
    expect($css)->toContain('.settings-toolbar :is(button,.button,select,.settings-more-actions>summary){min-height:2.5rem')
        ->toContain('.settings-more-actions>summary{display:inline-flex;justify-content:center;white-space:nowrap}');
});










