<?php

declare(strict_types=1);

namespace Zoosper\AdminGrid\Tests\Unit;

use Zoosper\AdminGrid\GridViewState;
use Zoosper\AdminGrid\GridWorkspaceCompletePageRenderer;
use Zoosper\AdminGrid\GridWorkspaceNavigation;
use Zoosper\AdminGrid\GridWorkspaceNavigationRenderer;
use Zoosper\AdminGrid\GridWorkspacePage;
use Zoosper\Pagination\Pager;
use Zoosper\Grid\GridColumn;
use Zoosper\Grid\GridCriteria;
use Zoosper\Grid\GridDefinition;

test('complete page presents workspace rows and navigation in stable order', function (): void {
    $state = new GridViewState(
        definition: new GridDefinition('Pages', [new GridColumn('id', 'ID')]),
        criteria: new GridCriteria(new Pager(1, 20), 'id', 'desc'),
        visibleColumns: ['id'],
        columnOrder: ['id'],
        bookmarks: [],
    );
    $page = new GridWorkspacePage($state, '<section>Workspace</section>', '<table>Rows</table>');
    $complete = (new GridWorkspaceCompletePageRenderer(
        new GridWorkspaceNavigationRenderer(),
    ))->render($page, new GridWorkspaceNavigation(
        previousUrl: null,
        nextUrl: '/admin/pages?page=2',
        sortUrls: ['id' => '/admin/pages?sort=id&dir=asc'],
        exportUrl: '/admin/pages/export',
    ));

    expect($complete->html())
        ->toStartWith('<section>Workspace</section><table>Rows</table>')
        ->toContain('aria-label="Grid navigation"')
        ->toContain('rel="next"')
        ->toContain('data-grid-export');
});

test('pagination metadata rejects impossible page ranges', function (): void {
    expect(fn () => new \Zoosper\AdminGrid\GridWorkspacePagination(3, 2, 40))
        ->toThrow(\InvalidArgumentException::class, 'cannot exceed');
});











