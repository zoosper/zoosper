<?php

declare(strict_types=1);

namespace Zoosper\AdminGrid\Tests\Unit;

use Zoosper\AdminGrid\GridViewState;
use Zoosper\AdminGrid\GridWorkspaceActiveBookmark;
use Zoosper\AdminGrid\GridWorkspaceDirtyState;
use Zoosper\AdminGrid\GridWorkspaceStateFingerprint;
use Zoosper\AdminGrid\GridWorkspaceStatusDecorator;
use Zoosper\AdminGrid\GridWorkspaceViewStatusRenderer;
use Zoosper\AdminGrid\GridWorkspaceViewStatusResolver;
use Zoosper\Pagination\Pager;
use Zoosper\Grid\GridColumn;
use Zoosper\Grid\GridCriteria;
use Zoosper\Grid\GridDefinition;

function decoratedStatusState(bool $changed = false): GridViewState
{
    $savedState = [
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
            'state' => $savedState,
            'is_default' => true,
        ]],
        activeBookmarkId: 7,
    );
}

function statusDecorator(): GridWorkspaceStatusDecorator
{
    return new GridWorkspaceStatusDecorator(
        new GridWorkspaceViewStatusResolver(
            new GridWorkspaceActiveBookmark(),
            new GridWorkspaceDirtyState(new GridWorkspaceStateFingerprint()),
        ),
        new GridWorkspaceViewStatusRenderer(),
    );
}

test('status is inserted once at the beginning of the existing toolbar', function (): void {
    $html = statusDecorator()->decorate(
        decoratedStatusState(),
        '<section data-grid-workspace><div class="grid-workspace__bar"><button>Filters</button></div></section>',
    );

    expect(substr_count($html, 'data-grid-view-status'))->toBe(1);
    expect($html)->toContain(
        '<div class="grid-workspace__bar"><div class="grid-workspace__view-status"',
    );
    expect($html)->toContain('Published pages');
    expect($html)->not->toContain('Unsaved changes');
});

test('decorated toolbar shows unsaved state after a persistent change', function (): void {
    $html = statusDecorator()->decorate(
        decoratedStatusState(true),
        '<section><div class="grid-workspace__bar"></div></section>',
    );

    expect($html)->toContain('Unsaved changes')
        ->toContain('role="status"');
});

test('decorator fails loudly rather than silently emitting detached status', function (): void {
    expect(fn (): string => statusDecorator()->decorate(
        decoratedStatusState(),
        '<section>No workspace toolbar</section>',
    ))->toThrow(\RuntimeException::class, 'toolbar marker was not found');
});
