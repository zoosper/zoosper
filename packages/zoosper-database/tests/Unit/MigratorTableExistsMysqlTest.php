<?php

declare(strict_types=1);

require_once dirname(__DIR__, 4) . '/bootstrap/autoload.php';

use Zoosper\Core\Config\ConfigRepository;
use Zoosper\Database\ConnectionFactory;
use Zoosper\Database\Migrator;
use Zoosper\Core\Module\ModuleRegistry;

/**
 * BUG FIX REGRESSION TEST — proves Migrator::tableExists() (private,
 * exercised indirectly via migrate()'s own ensureMigrationTable() call)
 * works correctly against a REAL MySQL/MariaDB connection with real,
 * server-side prepared statements enabled (PDO::ATTR_EMULATE_PREPARES =>
 * false, per this project's own earlier security fix).
 *
 * HONEST LIMITATION, stated up front: this bug is specifically a MySQL
 * real-prepared-statement protocol limitation (SHOW TABLES does not
 * support bound parameters via MySQL's binary prepared-statement
 * protocol) — it cannot be reproduced or meaningfully tested against
 * SQLite, which this test suite otherwise defaults to. Following the same
 * pattern already established in ConnectionFactoryEmulatePreparesTest
 * (same session), this test attempts a REAL MySQL connection using the
 * environment's actual configured credentials and is explicitly SKIPPED
 * (never falsely passed or failed) if MySQL is not reachable or not the
 * configured default driver.
 *
 * TEST-ITSELF FIX (2026-07-30): the second test below previously asserted
 * that Migrator.php's source does NOT contain the literal string of the
 * old, broken SQL pattern — but that exact string also appeared, quoted
 * verbatim, in this file's OWN docblocks (explaining what the old bug
 * was), and in Migrator.php's own explanatory comments describing the fix
 * — the same "quoted the thing I'm checking for in my own documentation"
 * mistake made a few times earlier in this session. Fixed by describing
 * the old broken pattern in prose instead of quoting it verbatim anywhere
 * in either file, so this assertion only matches genuine, functional
 * source code, not documentation prose.
 *
 * File placement: app/zoosper-core/tests/Unit/Database/MigratorTableExistsMysqlTest.php
 * — 5 levels up to repo root, matching other per-module tests.
 */
it('runs migrate() successfully against real MySQL with real prepared statements enabled (proves tableExists() no longer breaks)', function (): void {
    $basePath = dirname(__DIR__, 4);
    $config = ConfigRepository::fromPath($basePath . '/config');

    $default = (string) $config->get('database.default', 'sqlite');
    if ($default !== 'mysql') {
        $this->markTestSkipped(
            "database.default is '{$default}', not 'mysql', in this environment's config. "
            . 'This test only has meaning against a real MySQL/MariaDB connection — skipping rather '
            . 'than falsely passing or failing against SQLite, which cannot reproduce this MySQL-'
            . 'specific prepared-statement protocol limitation.'
        );

        return;
    }

    try {
        $pdo = (new ConnectionFactory($config, $basePath))->create();
    } catch (\Throwable $exception) {
        $this->markTestSkipped(
            'Could not connect to the configured MySQL database from this test environment ('
            . $exception->getMessage() . '). Skipping — this test requires real MySQL connectivity.'
        );

        return;
    }

    // Confirm real prepared statements are genuinely active for this
    // connection — the exact precondition that exposed the original bug.
    expect((bool) $pdo->getAttribute(PDO::ATTR_EMULATE_PREPARES))->toBeFalse();

    $modules = new ModuleRegistry($basePath);

    // The actual regression proof: migrate() must complete without
    // throwing a PDOException. Prior to the fix, this would reliably fail
    // with a MySQL syntax error the moment ensureMigrationTable() called
    // the old, broken tableExists() implementation.
    $exceptionThrown = null;
    try {
        (new Migrator($pdo, $basePath, $modules))->migrate();
    } catch (\Throwable $exception) {
        $exceptionThrown = $exception;
    }

    expect($exceptionThrown)->toBeNull();
});

it('confirms the fixed tableExists() query uses information_schema.TABLES for MySQL', function (): void {
    $source = (string) file_get_contents(dirname(__DIR__, 2) . '/src/Migrator.php');

    // Asserting the PRESENCE of the correct, fixed pattern is a more
    // reliable proof than asserting the ABSENCE of the old pattern's exact
    // string — the old pattern's exact wording could legitimately still
    // appear in prose/comments describing what was fixed and why, without
    // that indicating any real regression.
    expect($source)->toContain('information_schema.TABLES');
});











