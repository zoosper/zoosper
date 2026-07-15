<?php

declare(strict_types=1);

namespace Zoosper\Core\Tests\Unit\Schema;

/**
 * Regression tests locking in the declarative schema validation rules.
 *
 * Phase 1.29 Step 1 - these pin SchemaValidator's behaviour before the
 * schema-engine unification, so valid schemas keep passing and misconfigured
 * schemas keep being rejected with errors.
 *
 * PCI-aware: no secrets are used in these tests.
 */

use Zoosper\Core\Schema\SchemaRegistry;
use Zoosper\Core\Schema\SchemaTable;
use Zoosper\Core\Schema\SchemaValidator;

/**
 * Wrap a single table in a registry for validation.
 */
function registryWith(SchemaTable $table): SchemaRegistry
{
    $registry = new SchemaRegistry();
    $registry->addTable($table);

    return $registry;
}

test('a valid schema passes', function () {
    $registry = registryWith(new SchemaTable(
        name: 'items',
        columns: [
            'id' => ['type' => 'integer', 'primary' => true, 'auto_increment' => true],
            'title' => ['type' => 'string', 'length' => 120, 'nullable' => false],
        ],
        indexes: [
            'idx_items_title' => ['columns' => ['title']],
        ],
    ));

    $result = (new SchemaValidator())->validate($registry);

    expect($result->isValid())->toBeTrue();
    expect($result->errors)->toBe([]);
});

test('rejects an unsupported column type', function () {
    $registry = registryWith(new SchemaTable(
        name: 'bad_types',
        columns: [
            'id' => ['type' => 'integer', 'primary' => true, 'auto_increment' => true],
            'shape' => ['type' => 'geometry'],
        ],
    ));

    expect((new SchemaValidator())->validate($registry)->isValid())->toBeFalse();
});

test('rejects a nullable primary column', function () {
    $registry = registryWith(new SchemaTable(
        name: 'bad_primary',
        columns: [
            'id' => ['type' => 'integer', 'primary' => true, 'nullable' => true],
        ],
    ));

    expect((new SchemaValidator())->validate($registry)->isValid())->toBeFalse();
});

test('rejects an index referencing a missing column', function () {
    $registry = registryWith(new SchemaTable(
        name: 'ghost_index',
        columns: [
            'id' => ['type' => 'integer', 'primary' => true, 'auto_increment' => true],
        ],
        indexes: [
            'idx_ghost' => ['columns' => ['ghost']],
        ],
    ));

    expect((new SchemaValidator())->validate($registry)->isValid())->toBeFalse();
});

test('rejects a table with no columns', function () {
    $registry = registryWith(new SchemaTable(name: 'empty', columns: [], indexes: []));

    expect((new SchemaValidator())->validate($registry)->isValid())->toBeFalse();
});
