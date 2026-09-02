<?php

declare(strict_types=1);

namespace Zoosper\AdminGrid\Tests\Unit;

use Zoosper\AdminGrid\GridCompactToolbarRenderer;

test('compact toolbar exposes toggles count page size and export', function (): void {
    $html = (new GridCompactToolbarRenderer())->render('Published pages', true, 50, 2, '/admin/pages/export');
    expect($html)->toContain('Filters (2)')->toContain('data-grid-toggle="columns"')
        ->toContain('value="50" selected')->toContain('data-grid-export')
        ->toContain('Unsaved')->not->toContain('<script');
});











