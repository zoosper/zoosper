<?php

declare(strict_types=1);

namespace Zoosper\Page\Tests\Unit\Schema;

/**
 * Regression tests for the page module's declarative schema.
 *
 * Phase 1.29 Step 4 - locks in that the page module declares
 * page_site_assignments plus the pages column additions (SEO metadata + content
 * format/json, folded from the legacy database/schema/*.php files) and validates
 * under the unified engine.
 *
 * PCI-aware: schema describes structure only; no secrets here.
 */

use Zoosper\Core\Schema\SchemaLoader;
use Zoosper\Core\Schema\SchemaRegistry;
use Zoosper\Core\Schema\SchemaTable;
use Zoosper\Core\Schema\SchemaValidator;

/** @return array<string, mixed> */
function pageSchemaConfig(): array
{
    return require dirname(__DIR__, 3) . '/config/db_schema.php';
}

/** @return list<SchemaTable> */
function loadPageTables(): array
{
    $loader = (new \ReflectionClass(SchemaLoader::class))->newInstanceWithoutConstructor();

    return $loader->tablesFromConfig(pageSchemaConfig(), 'zoosper-page/config/db_schema.php', 'zoosper-page');
}

/**
 * @param list<SchemaTable> $tables
 */
function findPageTable(array $tables, string $name): ?SchemaTable
{
    foreach ($tables as $table) {
        if ($table->name === $name) {
            return $table;
        }
    }

    return null;
}

test('page schema declares page_site_assignments and pages', function () {
    $tables = loadPageTables();

    expect(findPageTable($tables, 'page_site_assignments'))->not->toBeNull();
    expect(findPageTable($tables, 'pages'))->not->toBeNull();
});

test('pages declares SEO and content columns', function () {
    $pages = findPageTable(loadPageTables(), 'pages');

    expect($pages)->not->toBeNull();
    expect($pages->columns)->toHaveKeys([
        'meta_title',
        'meta_description',
        'meta_keywords',
        'canonical_url',
        'content_format',
        'content_json',
    ]);
});

test('page schema validates under the unified engine', function () {
    $registry = new SchemaRegistry();
    foreach (loadPageTables() as $table) {
        $registry->addTable($table);
    }

    expect((new SchemaValidator())->validate($registry)->isValid())->toBeTrue();
});
