<?php

declare(strict_types=1);

namespace Zoosper\AdminGrid\Tests\Unit;

use Zoosper\AdminGrid\GridViewState;
use Zoosper\AdminGrid\GridWorkspaceDirtyState;
use Zoosper\AdminGrid\GridWorkspaceStateFingerprint;
use Zoosper\Pagination\Pager;
use Zoosper\Grid\GridColumn;
use Zoosper\Grid\GridCriteria;
use Zoosper\Grid\GridDefinition;

function dirtyStateFixture(int $page = 1): GridViewState
{
    return new GridViewState(
        definition: new GridDefinition('Pages', [
            new GridColumn('id', 'ID'),
            new GridColumn('title', 'Title'),
            new GridColumn('actions', 'Actions'),
        ]),
        criteria: new GridCriteria(
            new Pager($page, 50),
            'title',
            'asc',
            ['status' => 'published', 'site_id' => ['4', '9']],
        ),
        visibleColumns: ['id', 'title', 'actions'],
        columnOrder: ['title', 'id', 'actions'],
        bookmarks: [],
        activeBookmarkId: 7,
    );
}

function savedDirtyStateFixture(): array
{
    return [
        'filters' => ['site_id' => ['4', '9'], 'status' => 'published'],
        'sort_by' => 'title',
        'sort_dir' => 'asc',
        'page_size' => 50,
        'visible_columns' => ['id', 'title', 'actions'],
        'column_order' => ['title', 'id', 'actions'],
    ];
}

test('equivalent resolved and saved states are clean regardless of filter key order', function (): void {
    $dirty = new GridWorkspaceDirtyState(new GridWorkspaceStateFingerprint());

    expect($dirty->isDirty(dirtyStateFixture(), savedDirtyStateFixture()))->toBeFalse();
});

test('pagination does not mark a saved view dirty', function (): void {
    $dirty = new GridWorkspaceDirtyState(new GridWorkspaceStateFingerprint());

    expect($dirty->isDirty(dirtyStateFixture(8), savedDirtyStateFixture()))->toBeFalse();
});

test('filter column visibility and order changes mark a saved view dirty', function (): void {
    $dirty = new GridWorkspaceDirtyState(new GridWorkspaceStateFingerprint());
    $saved = savedDirtyStateFixture();

    $changedFilter = $saved;
    $changedFilter['filters']['status'] = 'draft';
    expect($dirty->isDirty(dirtyStateFixture(), $changedFilter))->toBeTrue();

    $changedColumns = $saved;
    $changedColumns['visible_columns'] = ['id', 'actions'];
    expect($dirty->isDirty(dirtyStateFixture(), $changedColumns))->toBeTrue();

    $changedOrder = $saved;
    $changedOrder['column_order'] = ['id', 'title', 'actions'];
    expect($dirty->isDirty(dirtyStateFixture(), $changedOrder))->toBeTrue();
});

test('default workspace without a saved view is not presented as dirty', function (): void {
    $dirty = new GridWorkspaceDirtyState(new GridWorkspaceStateFingerprint());

    expect($dirty->isDirty(dirtyStateFixture(), null))->toBeFalse();
});











