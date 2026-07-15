<?php

declare(strict_types=1);

namespace Zoosper\Core\Tests\Unit\Schema;

/**
 * Regression tests for the unified SchemaLoader format parsing.
 *
 * Phase 1.29 - locks in the top-level ['tables' => ...] wrapper parsing and the
 * descriptive ZoosperException raised for the legacy flat format. The pure
 * tablesFromConfig() helper is exercised via a loader built without its
 * constructor, so no ModuleRegistry is required.
 *
 * Note: no ReflectionProperty::setAccessible() (deprecated on PHP 8.5).
 *
 * PCI-aware: no secrets are used in these tests.
 */

use Zoosper\Core\Exception\ZoosperException;
use Zoosper\Core\Schema\SchemaLoader;
use Zoosper\Core\Schema\SchemaTable;

/**
 * Build a SchemaLoader without its constructor (tablesFromConfig needs no deps).
 */
function makeSchemaLoader(): SchemaLoader
{
    return (new \ReflectionClass(SchemaLoader::class))->newInstanceWithoutConstructor();
}

test('parses tables from the wrapper format', function () {
    $loader = makeSchemaLoader();

    $tables = $loader->tablesFromConfig([
        'tables' => [
            'widgets' => [
                'columns' => [
                    'id' => ['type' => 'integer', 'primary' => true, 'auto_increment' => true],
                ],
                'indexes' => [
                    'idx_w' => ['columns' => ['id']],
                ],
            ],
        ],
    ]);

    expect($tables)->toHaveCount(1);
    expect($tables[0])->toBeInstanceOf(SchemaTable::class);
    expect($tables[0]->name)->toBe('widgets');
    expect($tables[0]->columns)->toHaveKey('id');
});

test('returns empty for an empty config', function () {
    expect(makeSchemaLoader()->tablesFromConfig([]))->toBe([]);
});

test('throws a descriptive error for the legacy flat format', function () {
    $loader = makeSchemaLoader();

    expect(fn () => $loader->tablesFromConfig(['widgets' => ['columns' => []]]))
        ->toThrow(ZoosperException::class);
});

test('throws when tables is not an array', function () {
    $loader = makeSchemaLoader();

    expect(fn () => $loader->tablesFromConfig(['tables' => 'nope']))
        ->toThrow(ZoosperException::class);
});

test('throws when a table definition is not an array', function () {
    $loader = makeSchemaLoader();

    expect(fn () => $loader->tablesFromConfig(['tables' => ['widgets' => 'nope']]))
        ->toThrow(ZoosperException::class);
});
