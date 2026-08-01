<?php

declare(strict_types=1);

namespace Zoosper\AdminGrid\Tests\Unit;

use Zoosper\AdminGrid\GridFeatureAcceptance;

test('complete compact workspace satisfies the closure contract', function (): void {
    $html = '<section data-grid-workspace><button data-grid-toggle="filters"></button>'
        . '<button data-grid-toggle="columns"></button><select data-grid-page-size></select>'
        . '<span class="grid-compact-status"></span><div data-grid-filter-chips></div>'
        . '<a data-grid-export></a><table class="grid-table"></table></section>';

    $report = (new GridFeatureAcceptance())->evaluate('admin.pages', $html);

    expect($report->isComplete())->toBeTrue();
    expect($report->failed)->toBe([]);
});

test('legacy page output cannot be declared complete', function (): void {
    $html = '<input type="text" name="site_id"><input type="hidden" name="page_size">'
        . '<table class="grid-table"></table>';

    $report = (new GridFeatureAcceptance())->evaluate('admin.pages', $html);

    expect($report->isComplete())->toBeFalse();
    expect($report->failed)->toContain('workspace')
        ->toContain('legacy_hidden_page_size_removed')
        ->toContain('legacy_site_id_input_removed');
});
