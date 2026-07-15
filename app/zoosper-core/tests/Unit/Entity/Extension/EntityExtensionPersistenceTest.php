<?php

declare(strict_types=1);

namespace Zoosper\Core\Tests\Unit\Entity\Extension;

/**
 * Regression tests for the entity extension-value persistence path.
 *
 * Phase 1.29 follow-up. Persists an ExtensionTable field through
 * EntityExtensionDataPersister and reads it back via EntityExtensionValueRepository
 * against an in-memory SQLite database. This is the extension-value equivalent of
 * AdminUserRepositoryLocaleTest and guards the fresh-install path that previously
 * threw "base table or view not found" because nothing created the table.
 *
 * Skips automatically if pdo_sqlite is unavailable.
 *
 * PCI-aware: only non-sensitive placeholder values are used.
 */

use PDO;
use Zoosper\Core\Entity\Extension\EntityExtensionDataPersister;
use Zoosper\Core\Entity\Extension\EntityExtensionValueRepository;
use Zoosper\Core\Entity\Save\EntityDataObject;
use Zoosper\Core\Entity\Save\FieldDefinition;
use Zoosper\Core\Entity\Save\FieldDefinitionRegistry;

/**
 * In-memory SQLite with the entity_extension_values table (unique constraint
 * mirrors the declarative schema's uq_entity_extension_field index).
 */
function makeExtensionPdo(): PDO
{
    $pdo = new PDO('sqlite::memory:');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    $pdo->exec(
        'CREATE TABLE entity_extension_values ('
        . ' id INTEGER PRIMARY KEY AUTOINCREMENT,'
        . ' entity_type TEXT NOT NULL,'
        . ' entity_id INTEGER NOT NULL,'
        . ' module TEXT NOT NULL,'
        . ' field_name TEXT NOT NULL,'
        . ' value_json TEXT NULL,'
        . ' created_at TEXT NOT NULL,'
        . ' updated_at TEXT NOT NULL,'
        . ' UNIQUE(entity_type, entity_id, module, field_name)'
        . ')'
    );

    return $pdo;
}

/**
 * A registry with one extension field (acme_blog.reading_time) plus a core
 * column, to prove only extension fields are persisted to the extension store.
 */
function makeExtensionRegistry(): FieldDefinitionRegistry
{
    $registry = new FieldDefinitionRegistry();
    $registry->register(FieldDefinition::extension('acme_blog', 'reading_time', 'Reading time'));
    $registry->register(FieldDefinition::coreColumn('title', 'Title'));

    return $registry;
}

test('persists an extension field and reads it back', function () {
    if (!in_array('sqlite', PDO::getAvailableDrivers(), true)) {
        $this->markTestSkipped('pdo_sqlite not available');
    }

    $repository = new EntityExtensionValueRepository(makeExtensionPdo());
    $persister = new EntityExtensionDataPersister($repository);

    $data = (new EntityDataObject())->addData(['title' => 'Hello', 'reading_time' => 5]);
    $persister->persist('page', 1, $data, makeExtensionRegistry());

    $values = $repository->findForModule('page', 1, 'acme_blog');

    expect($values)->toHaveKey('reading_time');
    expect($values['reading_time'])->toBe(5);
    expect($values)->not->toHaveKey('title'); // core column is not an extension value
});

test('upsert updates an existing extension value (ON CONFLICT path)', function () {
    if (!in_array('sqlite', PDO::getAvailableDrivers(), true)) {
        $this->markTestSkipped('pdo_sqlite not available');
    }

    $repository = new EntityExtensionValueRepository(makeExtensionPdo());
    $persister = new EntityExtensionDataPersister($repository);
    $registry = makeExtensionRegistry();

    $persister->persist('page', 1, (new EntityDataObject())->addData(['reading_time' => 5]), $registry);
    $persister->persist('page', 1, (new EntityDataObject())->addData(['reading_time' => 8]), $registry);

    $values = $repository->findForModule('page', 1, 'acme_blog');

    expect($values['reading_time'])->toBe(8);
    expect($values)->toHaveCount(1); // updated in place, not duplicated
});

test('stores values per module and entity independently', function () {
    if (!in_array('sqlite', PDO::getAvailableDrivers(), true)) {
        $this->markTestSkipped('pdo_sqlite not available');
    }

    $repository = new EntityExtensionValueRepository(makeExtensionPdo());
    $persister = new EntityExtensionDataPersister($repository);

    $persister->persist('page', 1, (new EntityDataObject())->addData(['reading_time' => 5]), makeExtensionRegistry());

    $seoRegistry = new FieldDefinitionRegistry();
    $seoRegistry->register(FieldDefinition::extension('seo', 'score', 'Score'));
    $persister->persist('page', 1, (new EntityDataObject())->addData(['score' => 9]), $seoRegistry);

    $blog = $repository->findForModule('page', 1, 'acme_blog');
    $seo = $repository->findForModule('page', 1, 'seo');

    expect($blog)->toHaveKey('reading_time');
    expect($blog)->not->toHaveKey('score');
    expect($seo)->toHaveKey('score');
    expect($seo)->not->toHaveKey('reading_time');
});
