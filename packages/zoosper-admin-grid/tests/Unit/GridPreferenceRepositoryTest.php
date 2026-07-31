<?php

declare(strict_types=1);

namespace Zoosper\AdminGrid\Tests\Unit;

use PDO;
use Zoosper\AdminGrid\GridPreferenceRepository;

function preferenceDatabase(): PDO
{
    $pdo = new PDO('sqlite::memory:');
    $pdo->exec('CREATE TABLE admin_grid_preferences (id INTEGER PRIMARY KEY AUTOINCREMENT, admin_user_id INTEGER NOT NULL, grid_key TEXT NOT NULL, visible_columns_json TEXT NOT NULL, updated_at TEXT NOT NULL)');
    $pdo->exec('CREATE UNIQUE INDEX idx_admin_grid_prefs_user_grid ON admin_grid_preferences(admin_user_id, grid_key)');
    return $pdo;
}

test('visible columns are isolated by admin user and grid', function (): void {
    $repository = new GridPreferenceRepository(preferenceDatabase());
    $repository->saveVisibleColumns(10, 'admin.pages', ['id', 'title']);
    $repository->saveVisibleColumns(11, 'admin.pages', ['id', 'status']);
    expect($repository->findVisibleColumns(10, 'admin.pages'))->toBe(['id', 'title']);
    expect($repository->findVisibleColumns(11, 'admin.pages'))->toBe(['id', 'status']);
    expect($repository->findVisibleColumns(10, 'admin.audit'))->toBeNull();
});

test('visible columns update and clear without duplicate rows', function (): void {
    $pdo = preferenceDatabase();
    $repository = new GridPreferenceRepository($pdo);
    $repository->saveVisibleColumns(10, 'admin.pages', ['id']);
    $repository->saveVisibleColumns(10, 'admin.pages', ['title']);
    expect($repository->findVisibleColumns(10, 'admin.pages'))->toBe(['title']);
    expect((int) $pdo->query('SELECT COUNT(*) FROM admin_grid_preferences')->fetchColumn())->toBe(1);
    $repository->clear(10, 'admin.pages');
    expect($repository->findVisibleColumns(10, 'admin.pages'))->toBeNull();
});
