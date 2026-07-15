<?php

declare(strict_types=1);

namespace Zoosper\Core\Tests\Unit\Schema;

/**
 * Regression tests for the zoosper-core declarative schema.
 *
 * Phase 1.29 follow-up - locks in that zoosper-core declares BOTH
 * schema_snapshots and entity_extension_values, and that the schema validates
 * under the unified engine. This guards the fresh-install creation of
 * entity_extension_values so the table can never silently go missing again
 * (previously it lived only in a dead database/schema/*.sql file that nothing
 * read, causing a "base table not found" on the first extension-field save).
 *
 * PCI-aware: schema describes structure only; no secrets here.
 */

use Zoosper\Core\Schema\SchemaLoader;
use Zoosper\Core\Schema\SchemaRegistry;
use Zoosper\Core\Schema\SchemaTable;
use Zoosper\Core\Schema\SchemaValidator;

/** @return array<string, mixed> */
function coreSchemaConfig(): array
{
    return require dirname(__DIR__, 3) . '/config/db_schema.php';
}

/** @return list<SchemaTable> */
function loadCoreTables(): array
{
    $loader = (new \ReflectionClass(SchemaLoader::class))->newInstanceWithoutConstructor();

    return $loader->tablesFromConfig(coreSchemaConfig(), 'zoosper-core/config/db_schema.php', 'zoosper-core');
}

/**
 * @param list<SchemaTable> $tables
 */
function findCoreTable(array $tables, string $name): ?SchemaTable
{
    foreach ($tables as $table) {
        if ($table->name === $name) {
            return $table;
        }
    }

    return null;
}

test('core schema declares schema_snapshots and entity_extension_values', function () {
    $tables = loadCoreTables();

    expect(findCoreTable($tables, 'schema_snapshots'))->not->toBeNull();
    expect(findCoreTable($tables, 'entity_extension_values'))->not->toBeNull();
});

test('entity_extension_values declares the expected columns', function () {
    $table = findCoreTable(loadCoreTables(), 'entity_extension_values');

    expect($table)->not->toBeNull();
    expect($table->columns)->toHaveKeys([
        'entity_type',
        'entity_id',
        'module',
        'field_name',
        'value_json',
        'created_at',
        'updated_at',
    ]);
});

test('entity_extension_values has a unique composite index', function () {
    $table = findCoreTable(loadCoreTables(), 'entity_extension_values');

    expect($table)->not->toBeNull();
    expect($table->indexes)->toHaveKey('uq_entity_extension_field');
    expect($table->indexes['uq_entity_extension_field']['unique'])->toBeTrue();
    expect($table->indexes['uq_entity_extension_field']['columns'])->toBe([
        'entity_type',
        'entity_id',
        'module',
        'field_name',
    ]);
});

test('core schema validates under the unified engine', function () {
    $registry = new SchemaRegistry();
    foreach (loadCoreTables() as $table) {
        $registry->addTable($table);
    }

    expect((new SchemaValidator())->validate($registry)->isValid())->toBeTrue();
});
