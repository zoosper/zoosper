<?php

declare(strict_types=1);

namespace Zoosper\AdminGrid\Tests\Unit;

use Zoosper\AdminGrid\GridViewState;
use Zoosper\AdminGrid\GridWorkspaceMutationFormsRenderer;
use Zoosper\Pagination\Pager;
use Zoosper\Grid\GridColumn;
use Zoosper\Grid\GridCriteria;
use Zoosper\Grid\GridDefinition;

function mutationFormState(?int $bookmarkId = 7): GridViewState
{
    return new GridViewState(
        definition: new GridDefinition('Pages', [
            new GridColumn('id', 'ID', toggleable: false),
            new GridColumn('title', 'Title'),
            new GridColumn('actions', 'Actions', toggleable: false),
        ]),
        criteria: new GridCriteria(
            new Pager(1, 50),
            'title',
            'asc',
            ['status' => 'published', 'site_id' => ['4', '9']],
        ),
        visibleColumns: ['id', 'title', 'actions'],
        columnOrder: ['title', 'id', 'actions'],
        bookmarks: [],
        activeBookmarkId: $bookmarkId,
    );
}

test('every mutation form uses POST and carries the supplied CSRF token', function (): void {
    $html = (new GridWorkspaceMutationFormsRenderer())->render(
        mutationFormState(),
        '/admin/pages/grid',
        '_csrf',
        'token-value',
    );

    expect(substr_count($html, 'method="post"'))->toBe(4);
    expect(substr_count($html, 'data-grid-column-state-form'))->toBe(2);
    expect(substr_count($html, 'name="_csrf" value="token-value"'))->toBe(4);
    expect(substr_count($html, 'name="view_name"'))->toBe(1);
    expect($html)->not->toContain('admin_user_id');
    expect($html)->not->toContain('grid_key');
    expect($html)->not->toContain('redirect');
});

test('complete view state is posted for save-view operations', function (): void {
    $html = (new GridWorkspaceMutationFormsRenderer())->render(
        mutationFormState(),
        '/admin/pages/grid',
        '_csrf',
        'token',
    );

    expect($html)->toContain('name="filters[status][]" value="published"')
        ->toContain('name="filters[site_id][]" value="4"')
        ->toContain('name="filters[site_id][]" value="9"')
        ->toContain('name="sort_by" value="title"')
        ->toContain('name="sort_dir" value="asc"')
        ->toContain('name="workspace_page_size" value="50"')
        ->toContain('name="visible_columns[]" value="id"')
        ->toContain('name="column_order[]" value="title"');
});

test('delete view is rendered only when a bookmark is active', function (): void {
    $renderer = new GridWorkspaceMutationFormsRenderer();
    $active = $renderer->render(mutationFormState(7), '/admin/pages/grid', '_csrf', 'token');
    $default = $renderer->render(mutationFormState(null), '/admin/pages/grid', '_csrf', 'token');

    expect($active)->toContain('name="bookmark_id" value="7"');
    expect($default)->not->toContain('name="bookmark_id"');
});

test('mutation forms reject missing CSRF data and external form actions', function (): void {
    $renderer = new GridWorkspaceMutationFormsRenderer();

    expect(fn (): string => $renderer->render(mutationFormState(), '/admin/pages/grid', '', 'token'))
        ->toThrow(\InvalidArgumentException::class, 'require a CSRF');
    expect(fn (): string => $renderer->render(mutationFormState(), 'https://example.invalid', '_csrf', 'token'))
        ->toThrow(\InvalidArgumentException::class, 'application-local');
});











