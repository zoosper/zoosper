<?php
declare(strict_types=1);
it('owns an idempotent creator index migration and nullable creator foreign key', function (): void {
    $root = dirname(__DIR__, 2);
    $schema = require $root . '/config/db_schema.php';
    $announcement = $schema['tables']['admin_announcements'];
    expect($announcement['indexes']['idx_admin_announcements_creator']['columns'])->toBe(['created_by_user_id'])
        ->and($announcement['foreign_keys']['fk_admin_announcements_creator']['on_delete'])->toBe('SET NULL');
    $pdo = new PDO('sqlite::memory:');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec('CREATE TABLE admin_announcements (id INTEGER PRIMARY KEY, created_by_user_id INTEGER NULL)');
    $migration = require $root . '/database/migrations/202609020001_index_announcement_creator.php';
    $empty = new \PDO('sqlite::memory:');
    $empty->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    $migration->up($empty, 'sqlite');

    $migration->up($pdo, 'sqlite');
    $migration->up($pdo, 'sqlite');
    expect((int) $pdo->query("SELECT COUNT(*) FROM sqlite_master WHERE type='index' AND name='idx_admin_announcements_creator'")->fetchColumn())->toBe(1);
});
