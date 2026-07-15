<?php

declare(strict_types=1);

namespace Zoosper\Core\Tests\Unit\Entity\Save;

/**
 * Regression tests for the entity save pipeline field classification.
 *
 * Phase 1.21 - First regression suite (co-located in zoosper-core).
 *
 * Guards the core guarantee: only CoreColumn field definitions may reach the
 * SQL write map. Extension-table and virtual fields (e.g. CSRF tokens) must be
 * excluded so unknown/unsafe POST fields are never blindly written to entity
 * tables. Built against the real FieldDefinition / FieldDefinitionRegistry API.
 */

use Zoosper\Core\Entity\Save\FieldDefinition;
use Zoosper\Core\Entity\Save\FieldDefinitionRegistry;
use Zoosper\Core\Entity\Save\FieldStorageType;

test('only core-column fields appear in the write map', function () {
    // Arrange - one field of each storage type.
    $registry = new FieldDefinitionRegistry();
    $registry->register(FieldDefinition::coreColumn('title', 'Title'));
    $registry->register(FieldDefinition::coreColumn('slug', 'Slug', 'url_key')); // custom column
    $registry->register(FieldDefinition::extension('seo', 'meta_description', 'Meta Description'));
    $registry->register(FieldDefinition::virtual('_csrf_token', 'CSRF Token'));

    // Act - build the safe core-column write map (field name => column name).
    $map = $registry->coreColumnWriteMap();

    // Assert - only core columns are present, with correct column names.
    expect($map)->toHaveKeys(['title', 'slug']);
    expect($map['title'])->toBe('title');
    expect($map['slug'])->toBe('url_key');            // custom column honoured
    expect($map)->not->toHaveKey('meta_description'); // extension-table excluded
    expect($map)->not->toHaveKey('_csrf_token');      // virtual excluded
});

test('registerMany and get round-trip preserves storage type', function () {
    // Arrange
    $registry = new FieldDefinitionRegistry();
    $registry->registerMany([
        FieldDefinition::coreColumn('title', 'Title'),
        FieldDefinition::extension('seo', 'meta_description', 'Meta Description'),
        FieldDefinition::virtual('_csrf_token', 'CSRF Token'),
    ]);

    // Act
    $title = $registry->get('title');

    // Assert
    expect($title)->toBeInstanceOf(FieldDefinition::class);
    expect($title->storageType)->toBe(FieldStorageType::CoreColumn);
    expect($registry->all())->toHaveCount(3);
    expect($registry->get('missing'))->toBeNull();
});
