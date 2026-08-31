<?php

declare(strict_types=1);

namespace Zoosper\AdminGrid\Tests\Unit;

use PDO;
use Zoosper\AdminGrid\GridBookmarkRepository;
use Zoosper\AdminGrid\GridPreferenceRepository;
use Zoosper\AdminGrid\GridStateNormaliser;
use Zoosper\AdminGrid\GridViewMutationService;
use Zoosper\Grid\GridColumn;
use Zoosper\Grid\GridDefinition;
use Zoosper\Grid\GridFilter;

function mutationDatabase(): PDO
{
    $pdo = new PDO('sqlite::memory:');
    $pdo->exec('CREATE TABLE admin_grid_preferences (id INTEGER PRIMARY KEY AUTOINCREMENT, admin_user_id INTEGER NOT NULL, grid_key TEXT NOT NULL, visible_columns_json TEXT NOT NULL, updated_at TEXT NOT NULL)');
    $pdo->exec('CREATE UNIQUE INDEX idx_admin_grid_prefs_user_grid ON admin_grid_preferences(admin_user_id, grid_key)');
    $pdo->exec('CREATE TABLE admin_grid_bookmarks (id INTEGER PRIMARY KEY AUTOINCREMENT, admin_user_id INTEGER NOT NULL, grid_key TEXT NOT NULL, name TEXT NOT NULL, state_json TEXT NOT NULL, is_default INTEGER NOT NULL DEFAULT 0, created_at TEXT NOT NULL, updated_at TEXT NOT NULL)');
    $pdo->exec('CREATE UNIQUE INDEX idx_admin_grid_bookmarks_user_grid_name ON admin_grid_bookmarks(admin_user_id, grid_key, name)');
    return $pdo;
}

function mutationDefinition(): GridDefinition
{
    return new GridDefinition('Pages', [
        new GridColumn('id', 'ID', sortable: true, toggleable: false),
        new GridColumn('title', 'Title', sortable: true),
        new GridColumn('site_name', 'Site'),
        new GridColumn('actions', 'Actions', toggleable: false),
    ], [new GridFilter('q', 'Search')], defaultSort: 'id');
}

/** @return array{0: GridViewMutationService, 1: GridPreferenceRepository, 2: GridBookmarkRepository} */
function mutationService(PDO $pdo): array
{
    $preferences = new GridPreferenceRepository($pdo);
    $bookmarks = new GridBookmarkRepository($pdo);
    return [
        new GridViewMutationService($preferences, $bookmarks, new GridStateNormaliser()),
        $preferences,
        $bookmarks,
    ];
}

test('visible-column save keeps ID and Actions and drops unknown columns', function (): void {
    $pdo = mutationDatabase();
    [$service, $preferences] = mutationService($pdo);

    $saved = $service->saveVisibleColumns(
        10,
        'admin.pages',
        mutationDefinition(),
        ['title', 'retired'],
    );

    expect($saved)->toBe(['title', 'id', 'actions']);
    expect($preferences->findVisibleColumns(10, 'admin.pages'))->toBe($saved);
});

test('column-preference save normalises and persists visibility and order together', function (): void {
    $pdo = mutationDatabase();
    [$service, $preferences] = mutationService($pdo);

    $saved = $service->saveColumnPreferences(
        10,
        'admin.pages',
        mutationDefinition(),
        ['title', 'retired'],
        ['title', 'site_name', 'retired', 'title'],
    );

    expect($saved)->toBe([
        'visible_columns' => ['title', 'id', 'actions'],
        'column_order' => ['title', 'site_name', 'id', 'actions'],
    ]);
    expect($preferences->findColumnPreferences(10, 'admin.pages'))->toBe($saved);
});

test('legacy visibility-only service calls preserve an existing saved order', function (): void {
    $pdo = mutationDatabase();
    [$service, $preferences] = mutationService($pdo);
    $preferences->saveColumnPreferences(
        10,
        'admin.pages',
        ['title'],
        ['title', 'id', 'actions', 'site_name'],
    );

    $service->saveVisibleColumns(10, 'admin.pages', mutationDefinition(), ['site_name']);

    expect($preferences->findColumnPreferences(10, 'admin.pages'))->toBe([
        'visible_columns' => ['site_name', 'id', 'actions'],
        'column_order' => ['title', 'id', 'actions', 'site_name'],
    ]);
});

test('bookmark save persists one normalised complete workspace state', function (): void {
    $pdo = mutationDatabase();
    [$service, , $bookmarks] = mutationService($pdo);

    $state = $service->saveBookmark(10, 'admin.pages', mutationDefinition(), 'My pages', [
        'filters' => ['q' => ' landing ', 'unknown' => 'discard'],
        'sort_by' => 'title',
        'sort_dir' => 'asc',
        'page_size' => 999,
        'visible_columns' => ['title'],
        'column_order' => ['title', 'site_name', 'retired'],
    ], true);

    expect($state['filters'])->toBe(['q' => 'landing']);
    expect($state['page_size'])->toBe(200);
    expect($state['visible_columns'])->toBe(['title', 'id', 'actions']);
    expect($state['column_order'])->toBe(['title', 'site_name', 'id', 'actions']);
    expect($bookmarks->allForUser(10, 'admin.pages'))->toHaveCount(1);
    expect($bookmarks->allForUser(11, 'admin.pages'))->toBe([]);
});

test('bookmark mutation rejects invalid identity fields', function (): void {
    [$service] = mutationService(mutationDatabase());

    expect(fn () => $service->saveBookmark(10, 'admin.pages', mutationDefinition(), '   ', []))
        ->toThrow(\InvalidArgumentException::class, 'cannot be empty');
    expect(fn () => $service->deleteBookmark(10, 'admin.pages', 0))
        ->toThrow(\InvalidArgumentException::class, 'positive integer');
});

test('reset and delete remain scoped to admin user and grid', function (): void {
    $pdo = mutationDatabase();
    [$service, $preferences, $bookmarks] = mutationService($pdo);
    $preferences->saveVisibleColumns(10, 'admin.pages', ['title']);
    $preferences->saveVisibleColumns(11, 'admin.pages', ['site_name']);
    $bookmarks->save(10, 'admin.pages', 'Mine', []);
    $bookmarkId = $bookmarks->allForUser(10, 'admin.pages')[0]['id'];

    $service->resetVisibleColumns(10, 'admin.pages');
    $service->deleteBookmark(11, 'admin.pages', $bookmarkId);

    expect($preferences->findVisibleColumns(10, 'admin.pages'))->toBeNull();
    expect($preferences->findVisibleColumns(11, 'admin.pages'))->toBe(['site_name']);
    expect($bookmarks->allForUser(10, 'admin.pages'))->toHaveCount(1);
});











