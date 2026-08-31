<?php

declare(strict_types=1);

namespace Zoosper\Page\Tests\Unit\Admin;

use Zoosper\AdminGrid\GridViewState;
use Zoosper\AdminGrid\GridWorkspaceQuery;
use Zoosper\Pagination\Pager;
use Zoosper\Grid\GridColumn;
use Zoosper\Grid\GridCriteria;
use Zoosper\Grid\GridDefinition;
use Zoosper\Page\Admin\PageGridLinks;

function pageLinksState(): GridViewState
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

test('Page links preserve resolved state across pagination sorting and export', function (): void {
    $links = new PageGridLinks(new GridWorkspaceQuery());
    $state = pageLinksState();

    expect($links->page($state, 5))->toContain('/admin/pages?')->toContain('page=5');
    expect($links->sort($state, 'id', 'desc'))->toContain('sort=id')->toContain('dir=desc');
    expect($links->export($state))->toStartWith('/admin/pages/export?')
        ->toContain('site_id%5B0%5D=4')
        ->toContain('bookmark_id=7');
});










