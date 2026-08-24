<?php

declare(strict_types=1);

namespace Zoosper\AdminGrid\Tests\Unit;

use Zoosper\AdminGrid\GridCompactFilterChipsRenderer;

test('filter chips escape labels and preserve repeated Site filters', function (): void {
    $html = (new GridCompactFilterChipsRenderer())->render([
        'status' => 'published',
        'site_id' => ['Main Website', 'Wholesale <Portal>'],
    ]);

    expect(substr_count($html, 'data-grid-remove-filter="site_id"'))->toBe(2);
    expect($html)
        ->toContain('<span class="grid-filter-chip__label">Status: published</span>')
        ->toContain('Wholesale &lt;Portal&gt;')
        ->not->toContain('Wholesale <Portal>');
});
