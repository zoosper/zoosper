<?php

declare(strict_types=1);

namespace Zoosper\Media\Tests\Unit\Schema;

use Zoosper\Core\Schema\SchemaLoader;
use Zoosper\Core\Schema\SchemaRegistry;
use Zoosper\Core\Schema\SchemaTable;
use Zoosper\Core\Schema\SchemaValidator;

/** @return array<string, mixed> */
function mediaSchemaConfig(): array
{
    return require dirname(__DIR__, 3) . '/config/db_schema.php';
}

/** @return list<SchemaTable> */
function loadMediaTables(): array
{
    $loader = (new \ReflectionClass(SchemaLoader::class))->newInstanceWithoutConstructor();

    return $loader->tablesFromConfig(mediaSchemaConfig(), 'zoosper-media/config/db_schema.php', 'zoosper-media');
}

test('media schema declares media_assets table', function () {
    $tables = loadMediaTables();

    expect($tables)->toHaveCount(1);
    expect($tables[0]->name)->toBe('media_assets');
    expect($tables[0]->columns)->toHaveKeys(['uuid', 'filename', 'mime_type', 'storage_path', 'public_path']);
});

test('media schema validates under the unified schema engine', function () {
    $registry = new SchemaRegistry();
    foreach (loadMediaTables() as $table) {
        $registry->addTable($table);
    }

    expect((new SchemaValidator())->validate($registry)->isValid())->toBeTrue();
});
