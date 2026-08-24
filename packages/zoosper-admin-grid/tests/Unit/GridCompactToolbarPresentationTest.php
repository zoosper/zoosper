<?php

declare(strict_types=1);

use Zoosper\AdminGrid\GridCompactToolbarRenderer;

it('renders default and feature-owned page-size capabilities without inventing values', function (): void {
    $renderer = new GridCompactToolbarRenderer();
    $default = $renderer->render('Default view', false, 20, 0);
    $storeOrders = $renderer->render(
        'Default view',
        false,
        5,
        0,
        '/admin/store-orders/export',
        viewAction: '/admin/store-orders',
        pageSizeOptions: [5, 10, 20, 50, 100],
    );

    expect($default)->toContain('<option value="200">200</option>')
        ->not->toContain('<option value="5"')
        ->and($storeOrders)->toContain('<option value="5" selected>5</option>')
        ->toContain('<option value="10">10</option>')
        ->toContain('<option value="100">100</option>')
        ->not->toContain('<option value="200"');
});

it('groups display view export and state controls for responsive presentation', function (): void {
    $html = (new GridCompactToolbarRenderer())->render('Default view', false, 20, 0);

    expect($html)->toContain('class="grid-compact-toolbar"')
        ->toContain('class="grid-compact-display-tools"')
        ->toContain('class="grid-compact-view-tools"')
        ->toContain('class="grid-compact-state"')
        ->toContain('aria-controls="grid-filters-panel"')
        ->toContain('aria-controls="grid-columns-panel"');
});
