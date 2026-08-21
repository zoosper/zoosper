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
use Zoosper\Page\Admin\PageGridNavigationBuilder;

function navigationState(): GridViewState
{
    return new GridViewState(
        definition: new GridDefinition('Pages', [
            new GridColumn('id', 'ID', sortable: true),
            new GridColumn('title', 'Title', sortable: true),
            new GridColumn('actions', 'Actions'),
        ]),
        criteria: new GridCriteria(
            new Pager(2, 20),
            'title',
            'asc',
            ['site_id' => ['4', '9']],
        ),
        visibleColumns: ['id', 'title', 'actions'],
        columnOrder: ['title', 'id', 'actions'],
        bookmarks: [],
        activeBookmarkId: 7,
    );
}

test('Page navigation keeps the resolved view in paging sorting and export links', function (): void {
    $navigation = (new PageGridNavigationBuilder(
        new PageGridLinks(new GridWorkspaceQuery()),
    ))->build(navigationState(), 2, 4);

    expect($navigation->previousUrl)->toContain('/admin/pages?')
        ->toContain('site_id%5B0%5D=4');
    expect($navigation->nextUrl)->toContain('page=3');
    expect($navigation->sortUrls['title'])->toContain('sort=title')
        ->toContain('dir=desc');
    expect($navigation->sortUrls)->not->toHaveKey('actions');
    expect($navigation->exportUrl)->toStartWith('/admin/pages/export?')
        ->toContain('bookmark_id=7');
});
