<?php

declare(strict_types=1);

namespace Zoosper\Page\Tests\Unit\Admin;

test('obsolete Admin compact v2 presentation is not required after package ownership', function (): void {
    $root = dirname(__DIR__, 5);
    $adminGridCss = (string) file_get_contents(
        $root . '/app/zoosper-admin/resources/assets/css/zoosper-grid.css',
    );
    $packageGridCss = $root . '/packages/zoosper-admin-grid/resources/admin/css/grid-compact-workspace.css';

    expect($adminGridCss)
        ->toContain('Compact Grid presentation is package-owned by zoosper/admin-grid.')
        ->not->toContain('BEGIN ZOOSPER COMPACT GRID V2')
        ->and(is_file($packageGridCss))->toBeTrue()
        ->and(is_file(
            $root . '/app/zoosper-admin/resources/assets/css/zoosper-grid-compact-v2.css',
        ))->toBeFalse();
});










