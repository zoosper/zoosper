<?php

declare(strict_types=1);

it('publishes content-versioned shared current-page selection assets', function (): void {
    $root=dirname(__DIR__,4);$assets=require $root.'/packages/zoosper-admin-grid/config/admin_assets.php';
    $script=$root.'/packages/zoosper-admin-grid/resources/admin/js/grid-page-selection.js';
    $hash=substr(hash('sha256', (string) preg_replace('~\r\n?~', "\n", (string) file_get_contents($script))),0,12);
    expect($assets['assets'])->toHaveKeys(['zoosper-admin-grid-page-selection-style','zoosper-admin-grid-page-selection-script'])
        ->and($assets['assets']['zoosper-admin-grid-page-selection-script']['path']??null)
        ->toBe('/asset/zoosper-admin-grid/js/grid-page-selection.js?v='.$hash);
});

it('requires unique non-empty row identities before enabling selection', function (): void {
    $root=dirname(__DIR__,4);$script=file_get_contents($root.'/packages/zoosper-admin-grid/resources/admin/js/grid-page-selection.js');
    expect($script)->not->toBeFalse()
        ->and($script)->toContain("/^id(?:\\s*[▲▼])?$/i")
        ->and($script)->toContain("identities.every((value) => value !== '')")
        ->and($script)->toContain('new Set(identities).size === identities.length')
        ->and($script)->toContain("checkbox.name = 'selected_ids[]'")
        ->and($script)->toContain('selectAll.indeterminate')
        ->and($script)->not->toContain('localStorage');
});

it('keeps executable bulk actions disabled and identifies the form field', function (): void {
    $root=dirname(__DIR__,4);$script=file_get_contents($root.'/packages/zoosper-admin-grid/resources/admin/js/grid-page-selection.js');
    expect($script)->toContain("action.name = 'bulk_action'")
        ->toContain('action.disabled = true')
        ->toContain("action.innerHTML = '<option>Bulk actions</option>'");
});
