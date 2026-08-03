<?php

declare(strict_types=1);

it('publishes the first safe shared bulk action', function (): void {
    $root=dirname(__DIR__,4);$assets=require $root.'/packages/zoosper-admin-grid/config/admin_assets.php';
    expect($assets['assets'])->toHaveKeys(['zoosper-admin-grid-export-selected-style','zoosper-admin-grid-export-selected-script']);
});

it('exports only explicitly selected rendered rows as escaped CSV', function (): void {
    $root=dirname(__DIR__,4);$script=file_get_contents($root.'/packages/zoosper-admin-grid/resources/admin/js/grid-export-selected.js');
    expect($script)->not->toBeFalse()
        ->and($script)->toContain("tbody > tr.is-selected")
        ->and($script)->toContain(".slice(1)")
        ->and($script)->toContain("replaceAll('\"', '\"\"')")
        ->and($script)->toContain("'text/csv;charset=utf-8'")
        ->and($script)->toContain("exportOption.textContent = 'Export selected'")
        ->and($script)->toContain('URL.revokeObjectURL(url)')
        ->and($script)->not->toContain('fetch(')
        ->and($script)->not->toContain('localStorage');
});
