<?php

declare(strict_types=1);

use Zoosper\Database\MigrationInterface;

it('safely skips execution when media_assets does not exist or media_processing_queue already exists', function (): void {
    $pdo = new PDO('sqlite::memory:');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $migrationFile = dirname(__DIR__, 3) . '/database/migrations/202608310001_create_media_queue_table.php';
    expect(is_file($migrationFile))->toBeTrue();

    /** @var MigrationInterface $migration */
    $migration = require $migrationFile;
    expect($migration)->toBeInstanceOf(MigrationInterface::class);
    expect($migration->name())->toBe('202608310001_create_media_queue_table');

    // Case 1: media_assets does NOT exist. Migration should gracefully no-op.
    $migration->up($pdo, 'sqlite');

    $tables = $pdo->query("SELECT name FROM sqlite_master WHERE type='table'")->fetchAll(PDO::FETCH_COLUMN);
    expect($tables)->not->toContain('media_processing_queue');

    // Case 2: media_assets DOES exist. Migration should create media_processing_queue.
    $pdo->exec('CREATE TABLE media_assets (id INTEGER PRIMARY KEY AUTOINCREMENT)');
    $migration->up($pdo, 'sqlite');

    $tablesAfter = $pdo->query("SELECT name FROM sqlite_master WHERE type='table'")->fetchAll(PDO::FETCH_COLUMN);
    expect($tablesAfter)->toContain('media_processing_queue');

    // Case 3: Running again is idempotent.
    $migration->up($pdo, 'sqlite');
    expect(true)->toBeTrue();
});
