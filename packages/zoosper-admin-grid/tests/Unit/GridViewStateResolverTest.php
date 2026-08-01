<?php

declare(strict_types=1);

namespace Zoosper\AdminGrid\Tests\Unit;

use PDO;
use Zoosper\AdminGrid\GridBookmarkRepository;
use Zoosper\AdminGrid\GridPreferenceRepository;
use Zoosper\AdminGrid\GridStateNormaliser;
use Zoosper\AdminGrid\GridViewStateResolver;
use Zoosper\Grid\GridColumn;
use Zoosper\Grid\GridDefinition;
use Zoosper\Grid\GridFilter;

function viewStateDatabase(): PDO
{
    $pdo = new PDO('sqlite::memory:');
    $pdo->exec('CREATE TABLE admin_grid_preferences (id INTEGER PRIMARY KEY AUTOINCREMENT, admin_user_id INTEGER NOT NULL, grid_key TEXT NOT NULL, visible_columns_json TEXT NOT NULL, updated_at TEXT NOT NULL)');
    $pdo->exec('CREATE UNIQUE INDEX idx_admin_grid_prefs_user_grid ON admin_grid_preferences(admin_user_id, grid_key)');
    $pdo->exec('CREATE TABLE admin_grid_bookmarks (id INTEGER PRIMARY KEY AUTOINCREMENT, admin_user_id INTEGER NOT NULL, grid_key TEXT NOT NULL, name TEXT NOT NULL, state_json TEXT NOT NULL, is_default INTEGER NOT NULL DEFAULT 0, created_at TEXT NOT NULL, updated_at TEXT NOT NULL)');
    $pdo->exec('CREATE UNIQUE INDEX idx_admin_grid_bookmarks_user_grid_name ON admin_grid_bookmarks(admin_user_id, grid_key, name)');

    return $pdo;
}

function viewStateDefinition(): GridDefinition
{
    return new GridDefinition(
        title: 'Pages',
        columns: [
            new GridColumn('id', 'ID', sortable: true, toggleable: false),
            new GridColumn('title', 'Title', sortable: true),
            new GridColumn('status', 'Status'),
            new GridColumn('actions', 'Actions', toggleable: false),
        ],
        filters: [new GridFilter('status', 'Status')],
        defaultSort: 'id',
        defaultSortDir: 'desc',
    );
}

function resolverFor(PDO $pdo): GridViewStateResolver
{
    return new GridViewStateResolver(
        new GridPreferenceRepository($pdo),
        new GridBookmarkRepository($pdo),
        new GridStateNormaliser(),
    );
}

test('default bookmark and saved columns resolve for the authenticated admin only', function (): void {
    $pdo = viewStateDatabase();
    $preferences = new GridPreferenceRepository($pdo);
    $bookmarks = new GridBookmarkRepository($pdo);
    $preferences->saveVisibleColumns(10, 'admin.pages', ['title']);
    $bookmarks->save(10, 'admin.pages', 'Published', [
        'filters' => ['status' => 'published'],
        'sort_by' => 'title',
        'sort_dir' => 'asc',
    ], true);
    $bookmarks->save(11, 'admin.pages', 'Other user', [
        'filters' => ['status' => 'draft'],
    ], true);

    $state = resolverFor($pdo)->resolve(10, 'admin.pages', viewStateDefinition());

    expect($state->criteria->filters)->toBe(['status' => 'published']);
    expect($state->criteria->sortBy)->toBe('title');
    expect($state->visibleColumns)->toBe(['title', 'id', 'actions']);
    expect($state->bookmarks)->toHaveCount(1);
    expect($state->definition->allColumnKeys())->toBe(['id', 'title', 'actions']);
});

test('query state overrides the selected bookmark after validation', function (): void {
    $pdo = viewStateDatabase();
    $bookmarks = new GridBookmarkRepository($pdo);
    $bookmarks->save(10, 'admin.pages', 'Drafts', [
        'filters' => ['status' => 'draft'],
        'sort_by' => 'id',
    ], true);

    $state = resolverFor($pdo)->resolve(10, 'admin.pages', viewStateDefinition(), [
        'filters' => ['status' => 'published'],
        'sort_by' => 'title',
        'sort_dir' => 'asc',
    ]);

    expect($state->criteria->filters)->toBe(['status' => 'published']);
    expect($state->criteria->sortBy)->toBe('title');
    expect($state->criteria->sortDir)->toBe('asc');
});

test('an explicit foreign bookmark id is ignored', function (): void {
    $pdo = viewStateDatabase();
    $bookmarks = new GridBookmarkRepository($pdo);
    $bookmarks->save(11, 'admin.pages', 'Foreign', ['filters' => ['status' => 'draft']]);
    $foreignId = $bookmarks->allForUser(11, 'admin.pages')[0]['id'];

    $state = resolverFor($pdo)->resolve(
        10,
        'admin.pages',
        viewStateDefinition(),
        bookmarkId: $foreignId,
    );

    expect($state->activeBookmarkId)->toBeNull();
    expect($state->criteria->filters)->toBe([]);
    expect($state->bookmarks)->toBe([]);
});
