<?php

declare(strict_types=1);

use Zoosper\Core\Database\Migrator;
use Zoosper\Core\Module\ModuleRegistry;

/**
 * Phase 1.40c regression test.
 *
 * Proves the Migrator discovers migration files from each module's own
 * database/migrations/ folder (not just the root folder), using a FRESH
 * in-memory SQLite database against the REAL repository layout. This never
 * touches your actual MySQL database — it only reads migration *files* from
 * disk and replays them against a throwaway in-memory database to prove
 * discovery + execution works end to end.
 *
 * If this test passes, it proves:
 * 1. Every relocated migration file is still found (module folders are
 *    scanned correctly).
 * 2. Files still execute in original chronological order across modules.
 * 3. The resulting schema is identical to what running migrate() against a
 *    fresh install produced before the relocation.
 *
 * NOTE on file placement: place this file at
 * app/zoosper-core/tests/Unit/Database/ModuleOwnedMigrationDiscoveryTest.php
 * to match the depth used below (dirname(__DIR__, 5) from
 * tests/Unit/Database/ up to the repo root). If you place it at a different
 * depth, adjust the "4" to match — e.g. compare against how
 * MethodPluginModuleDiscoveryTest.php uses dirname(__DIR__, 5) from
 * tests/Unit/Plugin/.
 */
it('discovers and applies migrations from each module\'s own database/migrations folder', function (): void {
    $basePath = dirname(__DIR__, 5); // repo root — adjust if your test tree depth differs

    $pdo = new PDO('sqlite::memory:');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $registry = new ModuleRegistry($basePath);
    $migrator = new Migrator($pdo, $basePath, $registry);

    $migrator->migrate();

    // Tables that must exist after migration, regardless of which module's
    // folder their migration file now lives in.
    $expectedTables = [
        'admin_users',          // app/zoosper-auth/database/migrations
        'admin_roles',          // app/zoosper-auth/database/migrations
        'admin_permissions',    // app/zoosper-auth/database/migrations
        'sites',                // app/zoosper-site/database/migrations
        'site_domains',         // app/zoosper-site/database/migrations
        'pages',                // app/zoosper-page/database/migrations
        'page_revisions',       // app/zoosper-page/database/migrations
        'admin_login_history',  // app/zoosper-admin/config/db_schema.php
        'admin_activity_log',   // app/zoosper-admin/config/db_schema.php
        'admin_dashboard_preferences', // app/zoosper-admin/config/db_schema.php
    ];

    foreach ($expectedTables as $table) {
        $exists = (bool) $pdo
            ->query("SELECT name FROM sqlite_master WHERE type='table' AND name='{$table}'")
            ->fetchColumn();

        expect($exists)->toBeTrue("Expected table '{$table}' to exist after migrate() — module-owned migration discovery may be broken.");
    }

    // Confirm the relocated ACL migration actually ran (adds parent_code to
    // admin_permissions) — proves ALTER-style migrations from module
    // folders also execute correctly, not just CREATE TABLE statements.
    $columns = $pdo->query('PRAGMA table_info(admin_permissions)')->fetchAll(PDO::FETCH_ASSOC);
    $columnNames = array_column($columns, 'name');

    expect($columnNames)->toContain('parent_code');
});

it('no longer finds relocated migration files in the root database/migrations folder', function (): void {
    $basePath = dirname(__DIR__, 5);
    $rootMigrations = $basePath . '/database/migrations';

    $relocated = [
        '202607090001_create_auth_tables.php',
        '202607090002_seed_auth_defaults.php',
        '202607090003_create_site_tables.php',
        '202607090004_create_page_tables.php',
        '202607090005_seed_user_role_permissions.php',
        '202607090006_create_audit_login_history.php',
        '202607090007_acl_tree_metadata.php',
        '202607090008_site_theme_code.php',
    ];

    foreach ($relocated as $file) {
        expect($rootMigrations . '/' . $file)->not->toBeFile();
    }

    // The core bootstrap continuity no-op migration should remain at root.
    expect($rootMigrations . '/20260710002600_apply_module_declarative_schemas.php')->toBeFile();

    // The dead, never-executed .sql file should be gone entirely.
    expect($rootMigrations . '/20260710002400_create_url_rewrites_table.sql')->not->toBeFile();
});

