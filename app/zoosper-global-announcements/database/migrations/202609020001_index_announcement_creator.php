<?php
declare(strict_types=1);
use Zoosper\Database\MigrationInterface;
use Zoosper\Database\Schema\SchemaInspector;
return new class implements MigrationInterface {
    public function name(): string
    {
        return '202609020001_index_announcement_creator';
    }
    public function up(PDO $pdo, string $driver): void
    {
        $inspector = new SchemaInspector($pdo, $driver);
        if (
            !$inspector->tableExists('admin_announcements')
            || !$inspector->columnExists('admin_announcements', 'created_by_user_id')
            || $inspector->indexExists('admin_announcements', 'idx_admin_announcements_creator')
        ) {
            return;
        }
        $pdo->exec('CREATE INDEX idx_admin_announcements_creator ON admin_announcements (created_by_user_id)');
    }
};
