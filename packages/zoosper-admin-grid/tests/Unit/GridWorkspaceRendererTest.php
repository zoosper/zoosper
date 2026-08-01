<?php

declare(strict_types=1);

namespace Zoosper\AdminGrid\Tests\Unit;

use Zoosper\AdminGrid\GridViewState;
use Zoosper\AdminGrid\GridWorkspaceRenderer;
use Zoosper\Core\Pagination\Pager;
use Zoosper\Grid\GridColumn;
use Zoosper\Grid\GridCriteria;
use Zoosper\Grid\GridDefinition;
use Zoosper\Grid\GridFilter;
use Zoosper\Grid\GridFilterOption;

function workspaceState(): GridViewState
{
    $definition = new GridDefinition('Pages', [
        new GridColumn('id', 'ID', toggleable: false),
        new GridColumn('title', 'Title'),
        new GridColumn('site_name', 'Site'),
        new GridColumn('actions', 'Actions', toggleable: false),
    ], [
        new GridFilter('q', 'Search'),
        new GridFilter('site_id', 'Site', 'multiselect', [
            new GridFilterOption('4', 'Main Website'),
            new GridFilterOption('9', 'Wholesale Portal'),
        ]),
    ]);

    return new GridViewState(
        definition: $definition,
        criteria: new GridCriteria(new Pager(1, 20), null, 'desc', ['site_id' => ['9']]),
        visibleColumns: ['id', 'title', 'actions'],
        columnOrder: ['id', 'title', 'site_name', 'actions'],
        bookmarks: [['id' => 3, 'name' => 'Published pages', 'state' => [], 'is_default' => true]],
        activeBookmarkId: 3,
    );
}

test('workspace exposes filters columns views and export from one toolbar', function (): void {
    $html = (new GridWorkspaceRenderer())->render(workspaceState(), '/admin/pages');

    expect($html)->toContain('data-grid-panel-toggle="filters"')
        ->toContain('data-grid-panel-toggle="columns"')
        ->toContain('data-grid-save-view')
        ->toContain('data-grid-export')
        ->toContain('Published pages (Default)');
});

test('ID and Actions are locked visible while other columns are configurable', function (): void {
    $html = (new GridWorkspaceRenderer())->render(workspaceState(), '/admin/pages');

    expect($html)->toContain('value="id" checked disabled')
        ->toContain('value="actions" checked disabled')
        ->toContain('value="title" checked')
        ->toContain('value="site_name"');
});

test('workspace supports pointer and keyboard column ordering', function (): void {
    $html = (new GridWorkspaceRenderer())->render(workspaceState(), '/admin/pages');

    expect($html)->toContain('draggable="true"')
        ->toContain('data-column-move="up"')
        ->toContain('data-column-move="down"')
        ->toContain('name="column_order[]"');
});

test('Site filter shows names and preserves multiselection', function (): void {
    $html = (new GridWorkspaceRenderer())->render(workspaceState(), '/admin/pages');

    expect($html)->toContain('name="site_id[]" multiple')
        ->toContain('value="4">Main Website</option>')
        ->toContain('value="9" selected>Wholesale Portal</option>');
});
