<?php

declare(strict_types=1);

namespace Zoosper\Page\Tests\Unit\Admin;

test('compact Page Grid layout is consolidated into the existing Admin Grid source asset', function (): void {
    $root = dirname(__DIR__, 5);
    $gridCssPath = $root . '/app/zoosper-admin/resources/assets/css/zoosper-grid.css';

    expect(is_file($gridCssPath))->toBeTrue();

    $gridCss = (string) file_get_contents($gridCssPath);

    expect($gridCss)
        ->toContain('BEGIN ZOOSPER COMPACT GRID V2')
        ->toContain('.admin-content > [data-grid-workspace]')
        ->toContain('[data-grid-filter-form]')
        ->toContain('.grid-compact-state select')
        ->toContain('.grid-compact-panel[hidden]');

    // Standalone compact source files were intentionally retired after their
    // rules were consolidated into the existing Admin Grid stylesheet.
    expect(is_file(
        $root . '/app/zoosper-admin/resources/assets/css/zoosper-grid-compact-v2.css',
    ))->toBeFalse();
});
