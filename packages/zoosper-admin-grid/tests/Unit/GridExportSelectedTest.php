<?php

declare(strict_types=1);

it('publishes the first safe shared bulk action', function (): void {
    $root = dirname(__DIR__, 4);
    $assets = require $root . '/packages/zoosper-admin-grid/config/admin_assets.php';

    expect($assets['assets'])->toHaveKeys([
        'zoosper-admin-grid-export-selected-style',
        'zoosper-admin-grid-export-selected-script',
        'zoosper-admin-grid-bulk-action-manifest-script',
    ]);
});

it('exports only explicitly selected rendered rows as escaped CSV', function (): void {
    $root = dirname(__DIR__, 4);
    $executor = file_get_contents(
        $root . '/packages/zoosper-admin-grid/resources/admin/js/grid-export-selected.js',
    );
    $manifest = file_get_contents(
        $root . '/packages/zoosper-admin-grid/resources/admin/js/grid-bulk-action-manifest.js',
    );

    expect($executor)->not->toBeFalse()
        ->and($manifest)->not->toBeFalse()
        ->and($executor)->toContain('tbody > tr.is-selected')
        ->and($executor)->toContain('.slice(1)')
        ->and($executor)->toContain("replaceAll('\"', '\"\"')")
        ->and($executor)->toContain("'text/csv;charset=utf-8'")
        ->and($executor)->toContain("action.value !== 'export.selected'")
        ->and($executor)->toContain('URL.revokeObjectURL(url)')
        ->and($executor)->not->toContain('exportOption')
        ->and($executor)->not->toContain('fetch(')
        ->and($executor)->not->toContain('localStorage')
        ->and($manifest)->toContain("definition.id === 'export.selected'")
        ->and($manifest)->toContain("action.add(new Option(String(definition.label), String(definition.id)))");
});
