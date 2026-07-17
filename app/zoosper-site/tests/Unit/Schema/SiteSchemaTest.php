<?php

declare(strict_types=1);

namespace Zoosper\Site\Tests\Unit\Schema;

use Zoosper\Core\Schema\SchemaLoader;
use Zoosper\Core\Schema\SchemaRegistry;
use Zoosper\Core\Schema\SchemaTable;
use Zoosper\Core\Schema\SchemaValidator;

/** @return array<string, mixed> */
function siteSchemaConfig(): array
{
    return require dirname(__DIR__, 3) . '/config/db_schema.php';
}

/** @return list<SchemaTable> */
function loadSiteTables(): array
{
    $loader = (new \ReflectionClass(SchemaLoader::class))->newInstanceWithoutConstructor();

    return $loader->tablesFromConfig(siteSchemaConfig(), 'zoosper-site/config/db_schema.php', 'zoosper-site');
}

/** @param list<SchemaTable> $tables */
function findSiteTable(array $tables, string $name): ?SchemaTable
{
    foreach ($tables as $table) {
        if ($table->name === $name) {
            return $table;
        }
    }

    return null;
}

test('site schema enriches the sites table with store-view dimensions', function () {
    $sites = findSiteTable(loadSiteTables(), 'sites');

    expect($sites)->not->toBeNull();
    expect($sites->columns)->toHaveKeys([
        'theme_code',
        'locale',
        'currency',
        'base_url',
        'website_code',
        'store_code',
        'store_view_code',
        'path_prefix',
    ]);
});

test('site schema validates under the unified engine', function () {
    $registry = new SchemaRegistry();
    foreach (loadSiteTables() as $table) {
        $registry->addTable($table);
    }

    expect((new SchemaValidator())->validate($registry)->isValid())->toBeTrue();
});