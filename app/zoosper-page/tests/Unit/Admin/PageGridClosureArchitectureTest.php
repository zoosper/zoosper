<?php

declare(strict_types=1);

namespace Zoosper\Page\Tests\Unit\Admin;

test('Page Grid closure keeps one modern rendering path', function (): void {
    $root = dirname(__DIR__, 5);
    $contract = (string) file_get_contents(
        $root . '/packages/zoosper-admin-grid/src/GridFeatureAcceptance.php',
    );

    expect($contract)->toContain('data-grid-workspace')
        ->toContain('data-grid-page-size')
        ->toContain('data-grid-export')
        ->toContain('legacy_site_id_input_removed');
});










