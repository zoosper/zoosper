<?php

declare(strict_types=1);

use Zoosper\Database\MigrationInterface;
use Zoosper\Database\Schema\SchemaInspector;

return new class implements MigrationInterface {
    public function name(): string { return '202610120001_add_media_queue_observability'; }

    public function up(PDO $pdo, string $driver): void
    {
        $inspector = new SchemaInspector($pdo, $driver);
        if (!$inspector->tableExists('media_processing_queue')) {
            return;
        }
        $columns = $inspector->columns('media_processing_queue');
        foreach (['started_at', 'completed_at'] as $column) {
            if (isset($columns[$column])) {
                continue;
            }
            $type = $driver === 'mysql' ? 'DATETIME' : 'TEXT';
            $pdo->exec("ALTER TABLE media_processing_queue ADD COLUMN {$column} {$type} NULL");
        }
        if ($driver === 'mysql') {
            $pdo->exec('CREATE INDEX idx_media_queue_status_created ON media_processing_queue (status, created_at)');
        } else {
            $pdo->exec('CREATE INDEX IF NOT EXISTS idx_media_queue_status_created ON media_processing_queue(status, created_at)');
        }
    }
};
