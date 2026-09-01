<?php

declare(strict_types=1);

namespace Zoosper\Media\Tests\Unit\Schema;

use Zoosper\Database\Schema\SchemaLoader;
use Zoosper\Database\Schema\SchemaRegistry;
use Zoosper\Database\Schema\SchemaTable;
use Zoosper\Database\Schema\SchemaValidator;

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

    expect($tables)->toHaveCount(3);
    $byName = [];
    foreach ($tables as $table) {
        $byName[$table->name] = $table;
    }
    expect($byName)->toHaveKeys(['media_assets', 'media_derivatives', 'media_processing_queue']);
    expect($byName['media_assets']->columns)->toHaveKeys(['uuid', 'filename', 'mime_type', 'storage_path', 'public_path']);
    expect($byName['media_derivatives']->columns)->toHaveKeys(['media_asset_id', 'profile', 'format', 'width', 'height', 'storage_path', 'public_path']);
    expect($byName['media_processing_queue']->columns)->toHaveKeys(['asset_id', 'plan_json', 'status', 'attempts']);
});

test('media schema validates under the unified schema engine', function () {
    $registry = new SchemaRegistry();
    foreach (loadMediaTables() as $table) {
        $registry->addTable($table);
    }

    expect((new SchemaValidator())->validate($registry)->isValid())->toBeTrue();
});











