<?php

declare(strict_types=1);

namespace Zoosper\Page\Tests\Unit\Admin;

test('obsolete compact v2 asset is not required after Grid CSS consolidation', function (): void {
    $root = dirname(__DIR__, 5);
    $gridCss = (string) file_get_contents(
        $root . '/app/zoosper-admin/resources/assets/css/zoosper-grid.css',
    );

    expect($gridCss)->toContain('BEGIN ZOOSPER COMPACT GRID V2');
    expect(is_file(
        $root . '/app/zoosper-admin/resources/assets/css/zoosper-grid-compact-v2.css',
    ))->toBeFalse();
});
