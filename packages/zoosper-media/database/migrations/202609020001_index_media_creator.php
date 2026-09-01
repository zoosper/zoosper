<?php
declare(strict_types=1);
use Zoosper\Database\MigrationInterface;
use Zoosper\Database\Schema\SchemaInspector;
return new class implements MigrationInterface {
    public function name(): string
    {
        return '202609020001_index_media_creator';
    }
    public function up(PDO $pdo, string $driver): void
    {
        $inspector = new SchemaInspector($pdo, $driver);
        if (
            !$inspector->tableExists('media_assets')
            || !$inspector->columnExists('media_assets', 'created_by')
            || $inspector->indexExists('media_assets', 'idx_media_assets_creator')
        ) {
            return;
        }
        $pdo->exec('CREATE INDEX idx_media_assets_creator ON media_assets (created_by)');
    }
};
