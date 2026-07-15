<?php

declare(strict_types=1);

namespace Zoosper\Auth\Tests\Unit\Schema;

/**
 * Regression tests for the auth module's declarative schema.
 *
 * Phase 1.29 Step 4 - locks in that the auth module declares the
 * admin_users.locale column addition (folded from the legacy
 * database/schema/admin_user_locale.php) and validates under the unified engine.
 *
 * PCI-aware: schema describes structure only; no secrets here.
 */

use Zoosper\Core\Schema\SchemaLoader;
use Zoosper\Core\Schema\SchemaRegistry;
use Zoosper\Core\Schema\SchemaTable;
use Zoosper\Core\Schema\SchemaValidator;

/** @return array<string, mixed> */
function authSchemaConfig(): array
{
    return require dirname(__DIR__, 3) . '/config/db_schema.php';
}

/** @return list<SchemaTable> */
function loadAuthTables(): array
{
    $loader = (new \ReflectionClass(SchemaLoader::class))->newInstanceWithoutConstructor();

    return $loader->tablesFromConfig(authSchemaConfig(), 'zoosper-auth/config/db_schema.php', 'zoosper-auth');
}

/**
 * @param list<SchemaTable> $tables
 */
function findTable(array $tables, string $name): ?SchemaTable
{
    foreach ($tables as $table) {
        if ($table->name === $name) {
            return $table;
        }
    }

    return null;
}

test('auth schema declares the admin_users.locale column', function () {
    $adminUsers = findTable(loadAuthTables(), 'admin_users');

    expect($adminUsers)->not->toBeNull();
    expect($adminUsers->columns)->toHaveKey('locale');
});

test('auth schema validates under the unified engine', function () {
    $registry = new SchemaRegistry();
    foreach (loadAuthTables() as $table) {
        $registry->addTable($table);
    }

    expect((new SchemaValidator())->validate($registry)->isValid())->toBeTrue();
});
