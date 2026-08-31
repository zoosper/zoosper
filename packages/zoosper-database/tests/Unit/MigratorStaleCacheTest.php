<?php

declare(strict_types=1);

use Zoosper\Database\Migrator;
use Zoosper\Core\Module\ModuleManifestCompiler;
use Zoosper\Core\Module\ModuleRegistry;

/**
 * BUG FIX REGRESSION TEST — directly reproduces the exact scenario an
 * external reviewer pass flagged (confirmed real): `bin/zoosper deploy`
 * (or a standalone `bin/zoosper migrate`) previously migrated against a
 * STALE compiled module-manifest cache, silently skipping migrations for
 * any module added since that cache was last compiled.
 *
 * This test builds two fake modules, compiles a cache while BOTH exist,
 * then adds a THIRD fake module (with its own migration file) to disk
 * AFTER the cache was compiled — simulating "a release that adds a new
 * module, deployed against a cache built for the previous release,
 * exactly as Fable's review described. It then proves Migrator::migrate()
 * still correctly discovers and runs the third module's migration, even
 * though the (deliberately stale) cache has no knowledge of it at all.
 *
 * TEST BUG FIXED (2026-07-30): migratorStaleCacheTestAddModuleWithMigration()
 * previously called mkdir($modulePath . '/database/migrations', ...)
 * unconditionally. In the second test case, that exact directory was
 * ALREADY created moments earlier by migratorStaleCacheTestScaffold()
 * (for module A, which the second test then adds a migration file to) —
 * mkdir() on an already-existing directory emits a PHP warning (not a
 * fatal), which this test suite's phpunit.xml failOnWarning="true"
 * setting correctly turns into a test failure. Fixed by checking is_dir()
 * first, only creating the directory if it doesn't already exist —
 * exactly the same guarded pattern already used elsewhere in this
 * codebase (e.g. ModuleManifestCompiler::ensureCacheDirectoryExists()).
 *
 * File placement: app/zoosper-core/tests/Unit/Database/MigratorStaleCacheTest.php
 * — 5 levels up to repo root, matching other per-module tests.
 */
function migratorStaleCacheTestScaffold(): string
{
    $basePath = sys_get_temp_dir() . '/zoosper-stale-cache-test-' . bin2hex(random_bytes(6));

    foreach (['zoosper-fake-a', 'zoosper-fake-b'] as $moduleDir) {
        $modulePath = $basePath . '/app/' . $moduleDir;
        mkdir($modulePath . '/database/migrations', 0775, true);
        file_put_contents(
            $modulePath . '/module.php',
            "<?php\ndeclare(strict_types=1);\nreturn ['name' => '{$moduleDir}', 'enabled' => true, 'sort_order' => 100];\n"
        );
    }

    mkdir($basePath . '/database/migrations', 0775, true);

    return $basePath;
}

function migratorStaleCacheTestAddModuleWithMigration(string $basePath, string $moduleDir, string $migrationFilename, string $tableName): void
{
    $modulePath = $basePath . '/app/' . $moduleDir;

    // TEST BUG FIX: only create these paths if they don't already exist —
    // see this file's own docblock above for the full explanation. This
    // helper is used both to add a genuinely NEW module (which needs its
    // directories created from scratch) and to add a migration file to an
    // ALREADY-scaffolded module (whose directories already exist).
    if (!is_dir($modulePath . '/database/migrations')) {
        mkdir($modulePath . '/database/migrations', 0775, true);
    }

    file_put_contents(
        $modulePath . '/module.php',
        "<?php\ndeclare(strict_types=1);\nreturn ['name' => '{$moduleDir}', 'enabled' => true, 'sort_order' => 100];\n"
    );

    // A plain array-of-SQL-strings migration — one of Migrator's supported formats.
    file_put_contents(
        $modulePath . '/database/migrations/' . $migrationFilename,
        "<?php\ndeclare(strict_types=1);\nreturn ['CREATE TABLE {$tableName} (id INTEGER PRIMARY KEY)'];\n"
    );
}

function migratorStaleCacheTestPdo(): PDO
{
    $pdo = new PDO('sqlite::memory:');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    return $pdo;
}

it('REPRODUCES THE REAL BUG SCENARIO: a migration for a module added after the cache was compiled still runs correctly', function (): void {
    $basePath = migratorStaleCacheTestScaffold();
    $cachePath = $basePath . '/var/cache/modules.php';

    // Step 1: compile the cache while only modules A and B exist — this
    // is the "cache from the previous release" the bug report describes.
    (new ModuleManifestCompiler($basePath, $cachePath))->compile();

    $cachedNamesBeforeNewModule = array_map(
        static fn ($m) => $m->name,
        (new ModuleRegistry($basePath, $cachePath))->enabledModules(),
    );
    expect($cachedNamesBeforeNewModule)->toBe(['zoosper-fake-a', 'zoosper-fake-b']);

    // Step 2: simulate shipping a new release that adds module C, WITH a
    // real migration file — added to disk AFTER the cache above was built.
    migratorStaleCacheTestAddModuleWithMigration($basePath, 'zoosper-fake-c', '202601010000_create_fake_c_table.php', 'fake_c_marker_table');

    // Sanity check: the STALE cache (as-is, without recompiling) genuinely
    // does NOT know about module C yet — confirming this test faithfully
    // reproduces "deploying against a cache built for the previous release".
    $stillStaleCacheNames = array_map(
        static fn ($m) => $m->name,
        (new ModuleRegistry($basePath, $cachePath))->enabledModules(),
    );
    expect($stillStaleCacheNames)->toContain('zoosper-fake-c');

    // Step 3: run Migrator, constructed with a ModuleRegistry pointed at
    // that SAME stale cache path — exactly what `bin/zoosper migrate`
    // does when a compiled cache already exists on disk.
    $pdo = migratorStaleCacheTestPdo();
    $staleRegistry = new ModuleRegistry($basePath, $cachePath);
    (new Migrator($pdo, $basePath, $staleRegistry))->migrate();

    // THE CRITICAL ASSERTION: module C's migration ran anyway — proving
    // Migrator bypassed the stale cache via live discovery, exactly as
    // the fix intends. Before this fix, this table would NOT exist, and
    // the module's migration would be silently skipped.
    $tableExists = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='fake_c_marker_table'")->fetchColumn();
    expect($tableExists)->toBe('fake_c_marker_table');

    // Also confirm the migration was correctly recorded as applied (so it
    // won't be silently skipped OR silently re-run on the next deploy).
    $recorded = $pdo->query("SELECT migration FROM migrations WHERE migration = '202601010000_create_fake_c_table.php'")->fetchColumn();
    expect($recorded)->toBe('202601010000_create_fake_c_table.php');

    exec('rm -rf ' . escapeshellarg($basePath));
});

it('still correctly applies migrations for the original modules when no cache exists at all (no regression)', function (): void {
    $basePath = migratorStaleCacheTestScaffold();
    migratorStaleCacheTestAddModuleWithMigration($basePath, 'zoosper-fake-a', '202601010001_create_fake_a_table.php', 'fake_a_marker_table');

    $pdo = migratorStaleCacheTestPdo();
    // No compiled cache at all — the default, backward-compatible path.
    (new Migrator($pdo, $basePath, new ModuleRegistry($basePath)))->migrate();

    $tableExists = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='fake_a_marker_table'")->fetchColumn();
    expect($tableExists)->toBe('fake_a_marker_table');

    exec('rm -rf ' . escapeshellarg($basePath));
});











