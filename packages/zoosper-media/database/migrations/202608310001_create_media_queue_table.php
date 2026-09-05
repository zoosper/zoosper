<?php

declare(strict_types=1);

use Zoosper\Database\MigrationInterface;
use Zoosper\Database\Schema\SchemaInspector;

return new class implements MigrationInterface {
    public function name(): string
    {
        return '202608310001_create_media_queue_table';
    }

    public function up(PDO $pdo, string $driver): void
    {
        $inspector = new SchemaInspector($pdo, $driver);
        if ($inspector->tableExists('media_processing_queue') || !$inspector->tableExists('media_assets')) {
            return;
        }

        if ($driver === 'mysql') {
            $engine = ' ENGINE=InnoDB DEFAULT CHARSET=utf8mb4';
            $pdo->exec("CREATE TABLE IF NOT EXISTS media_processing_queue (
                id INT AUTO_INCREMENT PRIMARY KEY,
                asset_id INT NOT NULL,
                plan_json TEXT NOT NULL,
                status VARCHAR(32) NOT NULL DEFAULT 'pending',
                attempts INT NOT NULL DEFAULT 0,
                error_message TEXT NULL,
                created_at DATETIME NOT NULL,
                updated_at DATETIME NOT NULL,
                INDEX idx_media_queue_status(status),
                FOREIGN KEY (asset_id) REFERENCES media_assets(id) ON DELETE CASCADE
            )" . $engine);
            return;
        }

        $pdo->exec("CREATE TABLE IF NOT EXISTS media_processing_queue (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            asset_id INTEGER NOT NULL,
            plan_json TEXT NOT NULL,
            status TEXT NOT NULL DEFAULT 'pending',
            attempts INTEGER NOT NULL DEFAULT 0,
            error_message TEXT NULL,
            created_at TEXT NOT NULL,
            updated_at TEXT NOT NULL,
            FOREIGN KEY (asset_id) REFERENCES media_assets(id) ON DELETE CASCADE
        )");
        $pdo->exec('CREATE INDEX IF NOT EXISTS idx_media_queue_status ON media_processing_queue(status)');
    }
};











