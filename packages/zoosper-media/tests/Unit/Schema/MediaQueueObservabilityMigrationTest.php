<?php
declare(strict_types=1);
it('guards observability columns and index before creation on every database driver', function (): void {
    $root = dirname(__DIR__, 3);
    $source = (string) file_get_contents($root . '/database/migrations/202610120001_add_media_queue_observability.php');
    expect($source)
        ->toContain("columnExists('media_processing_queue', \$column)")
        ->toContain("indexExists('media_processing_queue', 'idx_media_queue_status_created')")
        ->not->toContain("if (\$driver === 'mysql')");
});
it('is repeatable on sqlite after a partially applied observability migration', function (): void {
    $pdo = new PDO('sqlite::memory:');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec('CREATE TABLE media_processing_queue (id INTEGER PRIMARY KEY, status TEXT, created_at TEXT, started_at TEXT NULL)');
    $migration = require dirname(__DIR__, 3) . '/database/migrations/202610120001_add_media_queue_observability.php';
    $migration->up($pdo, 'sqlite');
    $migration->up($pdo, 'sqlite');
    $columns = $pdo->query('PRAGMA table_info(media_processing_queue)')->fetchAll(PDO::FETCH_COLUMN, 1);
    expect($columns)->toContain('started_at')->toContain('completed_at')
        ->and((int) $pdo->query("SELECT COUNT(*) FROM sqlite_master WHERE type='index' AND name='idx_media_queue_status_created'")->fetchColumn())->toBe(1);
});
