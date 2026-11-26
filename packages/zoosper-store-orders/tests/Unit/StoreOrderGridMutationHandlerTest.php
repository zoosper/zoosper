<?php

declare(strict_types=1);

namespace Zoosper\StoreOrders\Tests\Unit;

use PDO;
use Zoosper\AdminGrid\GridBookmarkRepository;
use Zoosper\AdminGrid\GridPreferenceRepository;
use Zoosper\AdminGrid\GridStateNormaliser;
use Zoosper\AdminGrid\GridViewMutationService;
use Zoosper\AdminGrid\GridWorkspaceMutationContract;
use Zoosper\StoreOrders\Admin\StoreOrderGridMutationHandler;
use Zoosper\StoreOrders\Admin\StoreOrderGridWorkspace;

test('store order views are made default only when requested and belong to the authenticated user', function (): void {
    $pdo = new PDO('sqlite::memory:');
    $pdo->exec('CREATE TABLE admin_grid_bookmarks (id INTEGER PRIMARY KEY AUTOINCREMENT, admin_user_id INTEGER NOT NULL, grid_key TEXT NOT NULL, name TEXT NOT NULL, state_json TEXT NOT NULL, is_default INTEGER NOT NULL DEFAULT 0, created_at TEXT NOT NULL, updated_at TEXT NOT NULL)');
    $pdo->exec('CREATE UNIQUE INDEX bookmarks_user_grid_name ON admin_grid_bookmarks(admin_user_id, grid_key, name)');
    $bookmarks = new GridBookmarkRepository($pdo);
    $handler = new StoreOrderGridMutationHandler(
        new GridViewMutationService(new GridPreferenceRepository($pdo), $bookmarks, new GridStateNormaliser()),
    );

    $handler->handle(10, GridWorkspaceMutationContract::SAVE_VIEW, ['view_name' => 'Ordinary', 'admin_user_id' => 11]);
    $handler->handle(10, GridWorkspaceMutationContract::SET_DEFAULT_VIEW, ['view_name' => 'Default', 'admin_user_id' => 11]);

    $defaults = [];
    foreach ($bookmarks->allForUser(10, StoreOrderGridWorkspace::GRID_KEY) as $row) {
        $defaults[$row['name']] = (bool) $row['is_default'];
    }
    expect($defaults)->toBe(['Default' => true, 'Ordinary' => false])
        ->and($bookmarks->allForUser(11, StoreOrderGridWorkspace::GRID_KEY))->toBe([]);
});

test('unsupported store order mutations are rejected', function (): void {
    $pdo = new PDO('sqlite::memory:');
    $handler = new StoreOrderGridMutationHandler(
        new GridViewMutationService(new GridPreferenceRepository($pdo), new GridBookmarkRepository($pdo), new GridStateNormaliser()),
    );

    expect(fn () => $handler->handle(10, 'unknown', []))
        ->toThrow(\InvalidArgumentException::class, 'Unsupported Store Orders grid action: unknown');
});