<?php

declare(strict_types=1);

namespace Zoosper\AdminGrid\Tests\Unit;

use Zoosper\AdminGrid\GridViewState;
use Zoosper\AdminGrid\GridWorkspaceQuery;
use Zoosper\Pagination\Pager;
use Zoosper\Grid\GridColumn;
use Zoosper\Grid\GridCriteria;
use Zoosper\Grid\GridDefinition;

function queryState(): GridViewState
{
    return new GridViewState(
        definition: new GridDefinition('Pages', [
            new GridColumn('id', 'ID'),
            new GridColumn('title', 'Title'),
            new GridColumn('actions', 'Actions'),
        ]),
        criteria: new GridCriteria(
            new Pager(3, 50),
            'title',
            'asc',
            ['q' => 'summer sale', 'status' => 'published', 'site_id' => ['4', '9']],
        ),
        visibleColumns: ['id', 'title', 'actions'],
        columnOrder: ['title', 'id', 'actions'],
        bookmarks: [],
        activeBookmarkId: 7,
    );
}

test('query preserves filters multiselect columns order and active view', function (): void {
    $parameters = (new GridWorkspaceQuery())->parameters(queryState(), 4);

    expect($parameters)->toBe([
        'q' => 'summer sale',
        'status' => 'published',
        'site_id' => ['4', '9'],
        'sort' => 'title',
        'dir' => 'asc',
        'page_size' => 50,
        'page' => 4,
        'bookmark_id' => 7,
        'visible_columns' => ['id', 'title', 'actions'],
        'column_order' => ['title', 'id', 'actions'],
    ]);
});

test('query URL uses RFC3986 encoding and accepts sort overrides', function (): void {
    $url = (new GridWorkspaceQuery())->url(
        '/admin/pages',
        queryState(),
        page: 2,
        sortBy: 'id',
        sortDir: 'desc',
    );

    expect($url)->toStartWith('/admin/pages?')
        ->toContain('q=summer%20sale')
        ->toContain('site_id%5B0%5D=4')
        ->toContain('site_id%5B1%5D=9')
        ->toContain('sort=id')
        ->toContain('dir=desc')
        ->toContain('bookmark_id=7');
});

test('query URL rejects external paths and never emits identity selectors', function (): void {
    $query = new GridWorkspaceQuery();
    $url = $query->url('/admin/pages/export', queryState());

    expect($url)->not->toContain('admin_user_id')
        ->not->toContain('grid_key')
        ->not->toContain('redirect');
    expect(fn (): string => $query->url('https://example.invalid', queryState()))
        ->toThrow(\InvalidArgumentException::class, 'application-local');
});
