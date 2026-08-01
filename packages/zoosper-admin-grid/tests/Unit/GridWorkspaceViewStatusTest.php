<?php

declare(strict_types=1);

namespace Zoosper\AdminGrid\Tests\Unit;

use Zoosper\AdminGrid\GridViewState;
use Zoosper\AdminGrid\GridWorkspaceActiveBookmark;
use Zoosper\AdminGrid\GridWorkspaceDirtyState;
use Zoosper\AdminGrid\GridWorkspaceStateFingerprint;
use Zoosper\AdminGrid\GridWorkspaceViewStatusRenderer;
use Zoosper\AdminGrid\GridWorkspaceViewStatusResolver;
use Zoosper\Core\Pagination\Pager;
use Zoosper\Grid\GridColumn;
use Zoosper\Grid\GridCriteria;
use Zoosper\Grid\GridDefinition;

function statusState(bool $changed = false): GridViewState
{
    $saved = [
        'filters' => ['status' => 'published'],
        'sort_by' => 'title',
        'sort_dir' => 'asc',
        'page_size' => 20,
        'visible_columns' => ['id', 'title', 'actions'],
        'column_order' => ['title', 'id', 'actions'],
    ];

    return new GridViewState(
        definition: new GridDefinition('Pages', [
            new GridColumn('id', 'ID'),
            new GridColumn('title', 'Title'),
            new GridColumn('actions', 'Actions'),
        ]),
        criteria: new GridCriteria(
            new Pager(1, 20),
            'title',
            'asc',
            ['status' => $changed ? 'draft' : 'published'],
        ),
        visibleColumns: ['id', 'title', 'actions'],
        columnOrder: ['title', 'id', 'actions'],
        bookmarks: [[
            'id' => 7,
            'name' => 'Published pages',
            'state' => $saved,
            'is_default' => true,
        ]],
        activeBookmarkId: 7,
    );
}

test('active saved view resolves a clean status', function (): void {
    $resolver = new GridWorkspaceViewStatusResolver(
        new GridWorkspaceActiveBookmark(),
        new GridWorkspaceDirtyState(new GridWorkspaceStateFingerprint()),
    );
    $status = $resolver->resolve(statusState());

    expect($status->label)->toBe('Published pages');
    expect($status->isSavedView)->toBeTrue();
    expect($status->isDirty)->toBeFalse();
});

test('changed saved view renders an accessible unsaved indicator', function (): void {
    $resolver = new GridWorkspaceViewStatusResolver(
        new GridWorkspaceActiveBookmark(),
        new GridWorkspaceDirtyState(new GridWorkspaceStateFingerprint()),
    );
    $html = (new GridWorkspaceViewStatusRenderer())->render(
        $resolver->resolve(statusState(true)),
    );

    expect($html)->toContain('Published pages')
        ->toContain('Unsaved changes')
        ->toContain('role="status"')
        ->not->toContain('<script');
});

test('missing active bookmark safely falls back to Default view', function (): void {
    $state = statusState();
    $state = new GridViewState(
        $state->definition,
        $state->criteria,
        $state->visibleColumns,
        $state->columnOrder,
        $state->bookmarks,
        999,
    );
    $resolver = new GridWorkspaceViewStatusResolver(
        new GridWorkspaceActiveBookmark(),
        new GridWorkspaceDirtyState(new GridWorkspaceStateFingerprint()),
    );

    expect($resolver->resolve($state)->label)->toBe('Default view');
});
