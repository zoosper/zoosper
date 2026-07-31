<?php

declare(strict_types=1);

namespace Zoosper\AdminGrid\Tests\Unit;

use Zoosper\AdminGrid\GridStateNormaliser;
use Zoosper\Grid\GridColumn;
use Zoosper\Grid\GridDefinition;
use Zoosper\Grid\GridFilter;

function normaliserDefinition(): GridDefinition
{
    return new GridDefinition(
        title: 'Pages',
        columns: [
            new GridColumn('id', 'ID', sortable: true, toggleable: false),
            new GridColumn('title', 'Title', sortable: true),
            new GridColumn('status', 'Status'),
            new GridColumn('actions', 'Actions', toggleable: false),
        ],
        filters: [new GridFilter('q', 'Search'), new GridFilter('status', 'Status')],
        defaultSort: 'id',
        defaultSortDir: 'desc',
    );
}

test('saved state is constrained by the live grid definition', function (): void {
    $state = (new GridStateNormaliser())->normalise([
        'filters' => ['q' => ' hello ', 'unknown' => 'discard'],
        'sort_by' => 'title; DROP TABLE pages',
        'sort_dir' => 'sideways',
        'page_size' => 5000,
        'visible_columns' => ['title', 'retired', 'title'],
    ], normaliserDefinition());

    expect($state)->toBe([
        'filters' => ['q' => 'hello'],
        'sort_by' => 'id',
        'sort_dir' => 'desc',
        'page_size' => 200,
        'visible_columns' => ['title', 'id', 'actions'],
    ]);
});

test('page size has a minimum and non-toggleable columns remain visible', function (): void {
    $state = (new GridStateNormaliser())->normalise([
        'page_size' => 0,
        'visible_columns' => [],
    ], normaliserDefinition());

    expect($state['page_size'])->toBe(5);
    expect($state['visible_columns'])->toBe(['id', 'actions']);
});

test('normalised state creates shared grid criteria', function (): void {
    $criteria = (new GridStateNormaliser())->criteria([
        'filters' => ['status' => 'published'],
        'sort_by' => 'title',
        'sort_dir' => 'asc',
        'page_size' => 50,
    ], normaliserDefinition());

    expect($criteria->filters)->toBe(['status' => 'published']);
    expect($criteria->sortBy)->toBe('title');
    expect($criteria->sortDir)->toBe('asc');
    expect($criteria->pager->pageSize)->toBe(50);
});
