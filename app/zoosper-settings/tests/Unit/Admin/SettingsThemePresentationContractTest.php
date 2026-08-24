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
