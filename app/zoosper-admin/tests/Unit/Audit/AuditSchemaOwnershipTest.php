<?php

declare(strict_types=1);

namespace Zoosper\Admin\Tests\Unit\Audit;

test('audit tables are owned only by admin declarative schema', function (): void {
    $basePath = dirname(__DIR__, 5);
    $schemaFile = $basePath . '/app/zoosper-admin/config/db_schema.php';
    $legacyMigration = $basePath
        . '/app/zoosper-admin/database/migrations/202607090006_create_audit_login_history.php';
    $schema = require $schemaFile;
    $tables = $schema['tables'] ?? [];

    expect($tables)->toHaveKeys([
        'admin_login_history',
        'admin_activity_log',
    ]);
    expect($legacyMigration)->not->toBeFile();
});

test('audit declarative schema retains required repository columns and indexes', function (): void {
    $basePath = dirname(__DIR__, 5);
    $schema = require $basePath . '/app/zoosper-admin/config/db_schema.php';
    $tables = $schema['tables'] ?? [];

    $login = $tables['admin_login_history'] ?? [];
    expect($login['columns'] ?? [])->toHaveKeys([
        'id',
        'admin_user_id',
        'email',
        'status',
        'ip_address',
        'user_agent',
        'created_at',
    ]);
    expect($login['indexes'] ?? [])->toHaveKeys([
        'idx_admin_login_history_user',
        'idx_admin_login_history_email',
        'idx_admin_login_history_status',
        'idx_admin_login_history_created',
    ]);

    $activity = $tables['admin_activity_log'] ?? [];
    expect($activity['columns'] ?? [])->toHaveKeys([
        'id',
        'admin_user_id',
        'actor_email',
        'action',
        'entity_type',
        'entity_id',
        'summary',
        'metadata_json',
        'ip_address',
        'user_agent',
        'created_at',
    ]);
    expect($activity['indexes'] ?? [])->toHaveKeys([
        'idx_admin_activity_user',
        'idx_admin_activity_action',
        'idx_admin_activity_entity',
        'idx_admin_activity_created',
    ]);
});
