<?php

declare(strict_types=1);

it('keeps compact Grid disclosures mutually exclusive with accurate ARIA state', function (): void {
    $root = dirname(__DIR__, 4);
    $script = (string) file_get_contents(
        $root . '/packages/zoosper-admin-grid/resources/admin/js/grid-compact-workspace.js',
    );

    expect($script)->toContain('const setPanel=')
        ->toContain('const closeSiblingPanels=')
        ->toContain('if(target!==except)setPanel(target,false)')
        ->toContain("setAttribute('aria-expanded',String(open))")
        ->toContain('if(opening){closeSiblingPanels(target);closeSavedViewSettings()}')
        ->toContain('if(target)setPanel(target,false)');
});

it('closes only the adjacent saved-view surface when a compact panel opens', function (): void {
    $root = dirname(__DIR__, 4);
    $script = (string) file_get_contents(
        $root . '/packages/zoosper-admin-grid/resources/admin/js/grid-compact-workspace.js',
    );

    expect($script)->toContain("root.nextElementSibling?.matches('[data-grid-settings]')")
        ->toContain('settings.hidden=true')
        ->toContain("root.querySelector('[data-grid-settings-toggle]')")
        ->not->toContain("document.querySelector('[data-grid-settings]')")
        ->not->toContain('onclick=');
});

it('registers the compact disclosure runtime through the canonical Admin asset map', function (): void {
    $root = dirname(__DIR__, 4);
    $manifest = require $root . '/packages/zoosper-admin-grid/config/admin_assets.php';
    $asset = $manifest['assets']['zoosper-admin-grid-compact-workspace-script'] ?? [];
    $scriptPath = $root . '/packages/zoosper-admin-grid/resources/admin/js/grid-compact-workspace.js';
    $hash = substr(hash('sha256', (string) preg_replace('~\r\n?~', "\n", (string) file_get_contents($scriptPath))), 0, 12);

    expect($asset['type'] ?? null)->toBe('script')
        ->and($asset['path'] ?? null)
        ->toBe('/asset/zoosper-admin-grid/js/grid-compact-workspace.js?v=' . $hash)
        ->and($asset['sort_order'] ?? null)->toBe(80)
        ->and($asset['attributes']['defer'] ?? false)->toBeTrue();
});











