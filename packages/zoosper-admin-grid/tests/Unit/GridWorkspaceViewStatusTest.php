<?php

declare(strict_types=1);

namespace Zoosper\AdminGrid\Tests\Unit;

use Zoosper\AdminGrid\GridViewState;
use Zoosper\AdminGrid\GridWorkspaceActiveBookmark;
use Zoosper\AdminGrid\GridWorkspaceDirtyState;
use Zoosper\AdminGrid\GridWorkspaceStateFingerprint;
use Zoosper\AdminGrid\GridWorkspaceViewStatusRenderer;
use Zoosper\AdminGrid\GridWorkspaceViewStatusResolver;
use Zoosper\Pagination\Pager;
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

test('active bookmark preserves normalization of saved values', function (): void {
    $base = statusState();
    $find = static function (array $bookmarks, ?int $id) use ($base): ?array {
        return (new GridWorkspaceActiveBookmark())->find(new GridViewState(
            $base->definition,
            $base->criteria,
            $base->visibleColumns,
            $base->columnOrder,
            $bookmarks,
            $id,
        ));
    };

    expect($find([], null))->toBeNull();
    expect($find([['id' => 3]], 4))->toBeNull();
    expect($find([
        ['id' => '3', 'name' => null, 'state' => 'invalid', 'is_default' => '0'],
        ['id' => 3, 'name' => 'Second', 'state' => [], 'is_default' => true],
    ], 3))->toBe(['id' => 3, 'name' => 'Saved view', 'state' => [], 'is_default' => false]);
    expect($find([['is_default' => 'false']], 0))
        ->toBe(['id' => 0, 'name' => 'Saved view', 'state' => [], 'is_default' => true]);
    expect($find([['id' => 1, 'name' => 0, 'state' => ['x' => 1], 'is_default' => null]], 1))
        ->toBe(['id' => 1, 'name' => '0', 'state' => ['x' => 1], 'is_default' => false]);
});











