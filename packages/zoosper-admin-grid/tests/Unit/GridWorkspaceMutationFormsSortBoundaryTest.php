<?php

declare(strict_types=1);

use Zoosper\AdminGrid\GridViewState;
use Zoosper\AdminGrid\GridWorkspaceMutationFormsRenderer;
use Zoosper\Grid\GridColumn;
use Zoosper\Grid\GridCriteria;
use Zoosper\Grid\GridDefinition;
use Zoosper\Pagination\Pager;

/** Prove nullable sort values preserve the public saved-view HTML boundary. */
it('preserves and escapes the saved-view sort field', function (?string $sortBy, string $escaped): void {
    $state = new GridViewState(
        definition: new GridDefinition('Pages', [new GridColumn('title', 'Title')]),
        criteria: new GridCriteria(new Pager(1, 50), $sortBy, 'asc'),
        visibleColumns: ['title'],
        columnOrder: ['title'],
        bookmarks: [],
    );
    $html = (new GridWorkspaceMutationFormsRenderer())->render($state, '/admin/pages/grid', '_csrf', 'token');

    expect(substr_count($html, 'name="sort_by"'))->toBe(1)
        ->and($html)->toContain('<input type="hidden" name="sort_by" value="' . $escaped . '">')
        ->and($html)->toContain('name="sort_dir" value="asc"')
        ->and($html)->toContain('name="workspace_page_size" value="50"')
        ->and(substr_count($html, 'name="_csrf" value="token"'))->toBe(3)
        ->and($html)->toContain('value="save_view"')
        ->and($html)->toContain('value="set_default_view"');
})->with([
    'null' => [null, ''],
    'empty' => ['', ''],
    'ordinary' => ['title', 'title'],
    'zero string' => ['0', '0'],
    'HTML-sensitive' => ['"<&\'', '&quot;&lt;&amp;&#039;'],
]);
