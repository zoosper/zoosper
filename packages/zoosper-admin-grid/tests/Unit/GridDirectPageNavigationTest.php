<?php

declare(strict_types=1);

it('publishes a shared direct page navigation control', function (): void {
    $root=dirname(__DIR__,4);$assets=require $root.'/packages/zoosper-admin-grid/config/admin_assets.php';
    expect($assets['assets'])->toHaveKeys(['zoosper-admin-grid-page-jump-style','zoosper-admin-grid-page-jump-script']);
});

it('preserves Grid query state and validates direct page entry', function (): void {
    $root=dirname(__DIR__,4);$script=file_get_contents($root.'/packages/zoosper-admin-grid/resources/admin/js/grid-page-jump.js');
    expect($script)->not->toBeFalse()
        ->and($script)->toContain("params.delete('page')")
        ->and($script)->toContain('for (const [name, value] of params.entries())')
        ->and($script)->toContain("input.name = 'page'")
        ->and($script)->toContain("input.min = '1'")
        ->and($script)->toContain('input.max = String(totalPages)')
        ->and($script)->toContain('event.preventDefault()')
        ->and($script)->not->toContain('innerHTML');
});
