<?php
declare(strict_types=1);
it('owns an idempotent creator index migration and nullable creator foreign key', function (): void {
    $root = dirname(__DIR__, 3);
    $schema = require $root . '/config/db_schema.php';
    $asset = $schema['tables']['media_assets'];
    expect($asset['indexes']['idx_media_assets_creator']['columns'])->toBe(['created_by'])
        ->and($asset['foreign_keys']['fk_media_assets_creator']['on_delete'])->toBe('SET NULL');
    $pdo = new PDO('sqlite::memory:');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec('CREATE TABLE media_assets (id INTEGER PRIMARY KEY, created_by INTEGER NULL)');
    $migration = require $root . '/database/migrations/202609020001_index_media_creator.php';
    $empty = new \PDO('sqlite::memory:');
    $empty->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    $migration->up($empty, 'sqlite');

    $migration->up($pdo, 'sqlite');
    $migration->up($pdo, 'sqlite');
    expect((int) $pdo->query("SELECT COUNT(*) FROM sqlite_master WHERE type='index' AND name='idx_media_assets_creator'")->fetchColumn())->toBe(1);
});
