<?php

declare(strict_types=1);

namespace Zoosper\AdminGrid\Tests\Unit;

use Zoosper\AdminGrid\GridCompactWorkspaceRenderer;
use Zoosper\AdminGrid\GridViewState;
use Zoosper\Pagination\Pager;
use Zoosper\Grid\GridColumn;
use Zoosper\Grid\GridCriteria;
use Zoosper\Grid\GridDefinition;
use Zoosper\Grid\GridFilter;

it('renders native date picker controls for date filters', function (): void {
    $definition = new GridDefinition(
        'Orders',
        [new GridColumn('id', 'ID')],
        [new GridFilter('placed_from', 'Placed From', 'date')],
    );
    $state = new GridViewState(
        $definition,
        new GridCriteria(new Pager(1, 20), null, 'asc', ['placed_from' => '2026-08-02']),
        ['id'],
        ['id'],
        [],
        null,
    );
    $html = (new GridCompactWorkspaceRenderer())->render($state, '/admin/orders');

    expect($html)->toContain('type="date"')
        ->toContain('name="placed_from"')
        ->toContain('value="2026-08-02"');
});











