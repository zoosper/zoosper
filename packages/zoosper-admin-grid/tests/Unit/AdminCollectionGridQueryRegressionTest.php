<?php

declare(strict_types=1);

namespace Zoosper\AdminGrid\Tests\Unit;

use PDO;
use Zoosper\AdminGrid\AdminCollectionGridQuery;
use Zoosper\AdminGrid\GridBookmarkRepository;
use Zoosper\AdminGrid\GridPreferenceRepository;
use Zoosper\AdminGrid\GridStateNormaliser;
use Zoosper\AdminGrid\GridViewStateResolver;
use Zoosper\Core\Http\Request;
use Zoosper\Grid\GridColumn;
use Zoosper\Grid\GridColumnOrderer;
use Zoosper\Grid\GridDefinition;
use Zoosper\Grid\GridFilter;

function queryRegressionDefinition(): GridDefinition
{
    return new GridDefinition(
        title: 'Media',
        columns: [
            new GridColumn('id', 'ID', toggleable: false),
            new GridColumn('title', 'Title'),
            new GridColumn('status', 'Status'),
            new GridColumn('created_at', 'Created'),
            new GridColumn('actions', 'Actions', toggleable: false),
        ],
        filters: [new GridFilter('q', 'Search'), new GridFilter('status', 'Status')],
        defaultSort: 'created_at',
        defaultSortDir: 'desc',
    );
}

it('adapts flat GET controls to canonical Grid state without losing arrays', function (): void {
    $request = new Request('GET', '/admin/media', query: [
        'page' => '2',
        'page_size' => '50',
        'q' => 'what',
        'status' => '',
        'sort' => 'title',
        'dir' => 'asc',
        'visible_columns' => ['title', 'status'],
        'column_order' => ['id', 'title', 'status', 'created_at', 'actions'],
    ]);

    $definition = queryRegressionDefinition();
    $state = AdminCollectionGridQuery::values($request, $definition);
    $normalised = (new GridStateNormaliser())->normalise($state, $definition);

    expect($state['filters'])->toBe(['q' => 'what', 'status' => ''])
        ->and($state['sort_by'])->toBe('title')
        ->and($state['sort_dir'])->toBe('asc')
        ->and($normalised['filters'])->toBe(['q' => 'what'])
        ->and($normalised['page'])->toBe(2)
        ->and($normalised['page_size'])->toBe(50)
        ->and($normalised['visible_columns'])->toBe(['title', 'status', 'id', 'actions'])
        ->and($normalised['visible_columns'])->not->toContain('created_at');
});

it('preserves an intentional empty toggleable-column submission', function (): void {
    $definition = queryRegressionDefinition();
    $state = AdminCollectionGridQuery::values(new Request('GET', '/admin/media', query: [
        'columns_submitted' => '1',
        'column_order' => ['id', 'title', 'status', 'created_at', 'actions'],
    ]), $definition);
    $normalised = (new GridStateNormaliser())->normalise($state, $definition);

    expect($state)->toHaveKey('visible_columns')
        ->and($state['visible_columns'])->toBe([])
        ->and($normalised['visible_columns'])->toBe(['id', 'actions']);
});

it('replaces complete query lists instead of retaining bookmark list tails', function (): void {
    $pdo = new PDO('sqlite::memory:');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec('CREATE TABLE admin_grid_preferences (id INTEGER PRIMARY KEY AUTOINCREMENT, admin_user_id INTEGER NOT NULL, grid_key TEXT NOT NULL, visible_columns_json TEXT NOT NULL, updated_at TEXT NOT NULL)');
    $pdo->exec('CREATE UNIQUE INDEX idx_admin_grid_prefs_user_grid ON admin_grid_preferences(admin_user_id, grid_key)');
    $pdo->exec('CREATE TABLE admin_grid_bookmarks (id INTEGER PRIMARY KEY AUTOINCREMENT, admin_user_id INTEGER NOT NULL, grid_key TEXT NOT NULL, name TEXT NOT NULL, state_json TEXT NOT NULL, is_default INTEGER NOT NULL DEFAULT 0, created_at TEXT NOT NULL, updated_at TEXT NOT NULL)');
    $pdo->exec('CREATE UNIQUE INDEX idx_admin_grid_bookmarks_user_grid_name ON admin_grid_bookmarks(admin_user_id, grid_key, name)');

    $bookmarks = new GridBookmarkRepository($pdo);
    $bookmarks->save(10, 'admin.media', 'Default', [
        'visible_columns' => ['title', 'status', 'created_at'],
        'column_order' => ['id', 'title', 'status', 'created_at', 'actions'],
    ], true);

    $resolver = new GridViewStateResolver(
        new GridPreferenceRepository($pdo),
        $bookmarks,
        new GridStateNormaliser(),
        new GridColumnOrderer(),
    );
    $state = $resolver->resolve(10, 'admin.media', queryRegressionDefinition(), [
        'visible_columns' => ['title'],
        'column_order' => ['title', 'id', 'actions'],
    ]);

    expect($state->visibleColumns)->toBe(['title', 'id', 'actions'])
        ->and($state->visibleColumns)->not->toContain('status', 'created_at')
        ->and($state->columnOrder)->toBe(['title', 'id', 'actions', 'status', 'created_at']);
});
