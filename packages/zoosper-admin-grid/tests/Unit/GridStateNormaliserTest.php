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
        defaultSort: 'id', defaultSortDir: 'desc',
    );
}

test('saved state is constrained by the live grid definition', function (): void {
    $state=(new GridStateNormaliser())->normalise([
        'filters'=>['q'=>' hello ','unknown'=>'discard'],'sort_by'=>'title; DROP TABLE pages',
        'sort_dir'=>'sideways','page'=>-5,'page_size'=>5000,'visible_columns'=>['title','retired','title'],
    ],normaliserDefinition());
    expect($state)->toBe([
        'filters'=>['q'=>'hello'],'sort_by'=>'id','sort_dir'=>'desc','page'=>1,'page_size'=>200,
        'visible_columns'=>['title','id','actions'],'column_order'=>['id','title','status','actions'],
    ]);
});

test('page and page size are preserved in shared criteria', function (): void {
    $criteria=(new GridStateNormaliser())->criteria([
        'filters'=>['status'=>'published'],'sort_by'=>'title','sort_dir'=>'asc','page'=>3,'page_size'=>50,
    ],normaliserDefinition());
    expect($criteria->filters)->toBe(['status'=>'published'])
        ->and($criteria->sortBy)->toBe('title')->and($criteria->sortDir)->toBe('asc')
        ->and($criteria->pager->page)->toBe(3)->and($criteria->pager->pageSize)->toBe(50);
});

test('page size has a minimum and non-toggleable columns remain visible', function (): void {
    $state=(new GridStateNormaliser())->normalise(['page_size'=>0,'visible_columns'=>[]],normaliserDefinition());
    expect($state['page_size'])->toBe(5)->and($state['page'])->toBe(1)
        ->and($state['visible_columns'])->toBe(['id','actions']);
});











