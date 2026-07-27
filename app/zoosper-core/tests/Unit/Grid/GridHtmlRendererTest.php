<?php

declare(strict_types=1);

use Zoosper\Core\Grid\GridColumn;
use Zoosper\Core\Grid\GridCriteria;
use Zoosper\Core\Grid\GridDefinition;
use Zoosper\Core\Grid\GridFilter;
use Zoosper\Core\Grid\GridHtmlRenderer;
use Zoosper\Core\Pagination\Pager;
use Zoosper\Core\Pagination\PaginationResult;

/*
 * Grid Core (Phase A) behavioural tests for the shared HTML renderer.
 */

function rendererDefinition(bool $withFilters = true): GridDefinition
{
    return new GridDefinition(
        title: 'Sample',
        columns: [
            new GridColumn('created_at', 'Time', sortable: true),
            new GridColumn('action', 'Action', render: fn (mixed $v): string => '<code>' . htmlspecialchars((string) $v, ENT_QUOTES) . '</code>'),
        ],
        filters: $withFilters ? [new GridFilter('q', 'Search')] : [],
        defaultSort: 'created_at',
        defaultSortDir: 'desc',
    );
}

it('renders a sortable column header as a link with the toggled direction', function (): void {
    $definition = rendererDefinition();
    $criteria = GridCriteria::fromValues(['sort' => 'created_at', 'dir' => 'asc'], $definition);
    $result = new PaginationResult(items: [], total: 0, page: 1, pageSize: 20);

    $html = (new GridHtmlRenderer())->render($definition, $result, $criteria, '/admin/sample');

    expect($html)->toContain('grid-sortable')
        ->and($html)->toContain('dir=desc') // toggled FROM asc
        ->and($html)->toContain('sort=created_at');
});

it('renders a non-sortable column header as plain text, not a link', function (): void {
    $definition = rendererDefinition();
    $criteria = GridCriteria::fromValues([], $definition);
    $result = new PaginationResult(items: [], total: 0, page: 1, pageSize: 20);

    $html = (new GridHtmlRenderer())->render($definition, $result, $criteria, '/admin/sample');

    expect($html)->toContain('<th>Action</th>');
});

it('uses the empty message when there are no rows', function (): void {
    $definition = rendererDefinition();
    $criteria = GridCriteria::fromValues([], $definition);
    $result = new PaginationResult(items: [], total: 0, page: 1, pageSize: 20);

    $html = (new GridHtmlRenderer())->render($definition, $result, $criteria, '/admin/sample');

    expect($html)->toContain('No records found.')
        ->and($html)->not->toContain('grid-pagination__prev'); // no pagination when empty
});

it('renders rows using the column render callback and escapes plain values', function (): void {
    $definition = rendererDefinition();
    $criteria = GridCriteria::fromValues([], $definition);
    $result = new PaginationResult(
        items: [
            ['created_at' => '2026-07-27 08:00:00', 'action' => 'role.updated'],
            ['created_at' => '2026-07-26 <script>', 'action' => 'page.created'],
        ],
        total: 2,
        page: 1,
        pageSize: 20,
    );

    $html = (new GridHtmlRenderer())->render($definition, $result, $criteria, '/admin/sample');

    expect($html)->toContain('<code>role.updated</code>')
        ->and($html)->toContain('2026-07-27 08:00:00')
        // Unescaped custom value must be neutralised by the default escaping path.
        ->and($html)->toContain('2026-07-26 &lt;script&gt;')
        ->and($html)->not->toContain('2026-07-26 <script>');
});

it('renders pagination controls with correct prev/next state at the boundaries', function (): void {
    $definition = rendererDefinition();
    $criteria = GridCriteria::fromValues(['page' => '1'], $definition);

    $firstPage = new PaginationResult(items: [['created_at' => 'x', 'action' => 'y']], total: 45, page: 1, pageSize: 20);
    $htmlFirst = (new GridHtmlRenderer())->render($definition, $firstPage, $criteria, '/admin/sample');
    expect($htmlFirst)->toContain('grid-pagination__disabled') // prev disabled on page 1
        ->and($htmlFirst)->toContain('Page 1 of 3');

    $lastPage = new PaginationResult(items: [['created_at' => 'x', 'action' => 'y']], total: 45, page: 3, pageSize: 20);
    $criteriaLast = GridCriteria::fromValues(['page' => '3'], $definition);
    $htmlLast = (new GridHtmlRenderer())->render($definition, $lastPage, $criteriaLast, '/admin/sample');
    expect($htmlLast)->toContain('Page 3 of 3')
        ->and($htmlLast)->toContain('grid-pagination__next grid-pagination__disabled');
});

it('renders a filter bar with the current filter value pre-filled', function (): void {
    $definition = rendererDefinition();
    $criteria = GridCriteria::fromValues(['q' => 'alice'], $definition);
    $result = new PaginationResult(items: [], total: 0, page: 1, pageSize: 20);

    $html = (new GridHtmlRenderer())->render($definition, $result, $criteria, '/admin/sample');

    expect($html)->toContain('name="q"')
        ->and($html)->toContain('value="alice"');
});

it('omits the filter bar entirely when the grid declares no filters', function (): void {
    $definition = rendererDefinition(withFilters: false);
    $criteria = GridCriteria::fromValues([], $definition);
    $result = new PaginationResult(items: [], total: 0, page: 1, pageSize: 20);

    $html = (new GridHtmlRenderer())->render($definition, $result, $criteria, '/admin/sample');

    expect($html)->not->toContain('grid-filters');
});

it('shows an accurate "showing X-Y of Z" summary', function (): void {
    $definition = rendererDefinition();
    $criteria = GridCriteria::fromValues(['page' => '2'], $definition);
    $result = new PaginationResult(items: array_fill(0, 20, ['created_at' => 'x', 'action' => 'y']), total: 45, page: 2, pageSize: 20);

    $html = (new GridHtmlRenderer())->render($definition, $result, $criteria, '/admin/sample');

    expect($html)->toContain('Showing 21&ndash;40 of 45');
});
