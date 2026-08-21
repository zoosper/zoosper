<?php

declare(strict_types=1);

namespace Zoosper\AdminGrid\Tests\Unit;

use Zoosper\AdminGrid\GridViewState;
use Zoosper\AdminGrid\GridWorkspaceActiveBookmark;
use Zoosper\AdminGrid\GridWorkspaceDirtyState;
use Zoosper\AdminGrid\GridWorkspaceStateFingerprint;
use Zoosper\AdminGrid\GridWorkspaceViewActionsRenderer;
use Zoosper\AdminGrid\GridWorkspaceViewActionsResolver;
use Zoosper\Pagination\Pager;
use Zoosper\Grid\GridColumn;
use Zoosper\Grid\GridCriteria;
use Zoosper\Grid\GridDefinition;

function viewActionsState(bool $dirty = false, bool $default = false): GridViewState
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
        new GridDefinition('Pages', [
            new GridColumn('id', 'ID'),
            new GridColumn('title', 'Title'),
            new GridColumn('actions', 'Actions'),
        ]),
        new GridCriteria(new Pager(1, 20), 'title', 'asc', [
            'status' => $dirty ? 'draft' : 'published',
        ]),
        ['id', 'title', 'actions'],
        ['title', 'id', 'actions'],
        [['id' => 7, 'name' => 'Published pages', 'state' => $saved, 'is_default' => $default]],
        7,
    );
}

function viewActionsResolver(): GridWorkspaceViewActionsResolver
{
    return new GridWorkspaceViewActionsResolver(
        new GridWorkspaceActiveBookmark(),
        new GridWorkspaceDirtyState(new GridWorkspaceStateFingerprint()),
    );
}

test('active dirty view is prefilled and emphasises Update view', function (): void {
    $actions = viewActionsResolver()->resolve(viewActionsState(true));
    $html = (new GridWorkspaceViewActionsRenderer())->render($actions);

    expect($html)->toContain('value="Published pages"')
        ->toContain('>Update view</button>')
        ->toContain('data-grid-view-action="save_view" class="is-primary"')
        ->toContain('Make default')
        ->toContain('Delete view');
});

test('default saved view does not offer redundant Make default action', function (): void {
    $html = (new GridWorkspaceViewActionsRenderer())->render(
        viewActionsResolver()->resolve(viewActionsState(false, true)),
    );

    expect($html)->not->toContain('Make default')
        ->toContain('Delete view');
});

test('default workspace offers Save new view without destructive actions', function (): void {
    $state = viewActionsState();
    $state = new GridViewState(
        $state->definition,
        $state->criteria,
        $state->visibleColumns,
        $state->columnOrder,
        [],
        null,
    );
    $html = (new GridWorkspaceViewActionsRenderer())->render(
        viewActionsResolver()->resolve($state),
    );

    expect($html)->toContain('Save new view')
        ->not->toContain('Delete view')
        ->not->toContain('Make default');
});

test('view names are escaped before rendering into controls', function (): void {
    $state = viewActionsState();
    $bookmark = $state->bookmarks[0];
    $bookmark['name'] = '" autofocus onfocus="alert(1)';
    $state = new GridViewState(
        $state->definition,
        $state->criteria,
        $state->visibleColumns,
        $state->columnOrder,
        [$bookmark],
        7,
    );
    $html = (new GridWorkspaceViewActionsRenderer())->render(
        viewActionsResolver()->resolve($state),
    );

    expect($html)->toContain('&quot; autofocus onfocus=&quot;alert(1)')
        ->not->toContain(' onfocus="alert(1)');
});
