<?php

declare(strict_types=1);

use Zoosper\Database\Schema\SchemaLoader;
use Zoosper\Database\Schema\SchemaRegistry;
use Zoosper\Database\Schema\SchemaValidator;

it('declares the local and referenced columns required by the Page revision foreign key', function (): void {
    $root = dirname(__DIR__, 5);
    $config = require $root . '/app/zoosper-page/config/db_schema.php';

    expect($config['tables']['pages']['columns'])->toHaveKey('id')
        ->and($config['tables']['page_revisions']['columns'])->toHaveKeys(['id', 'page_id'])
        ->and($config['tables']['page_revisions']['foreign_keys']['fk_page_revisions_page'])
        ->toMatchArray([
            'columns' => ['page_id'],
            'referenced_table' => 'pages',
            'referenced_columns' => ['id'],
            'on_delete' => 'CASCADE',
        ]);
});

it('validates the standalone Page declarative schema with the revision relationship', function (): void {
    $root = dirname(__DIR__, 5);
    $loader = new ReflectionClass(SchemaLoader::class);
    $instance = $loader->newInstanceWithoutConstructor();
    $config = require $root . '/app/zoosper-page/config/db_schema.php';
    $registry = new SchemaRegistry();
    foreach ($instance->tablesFromConfig($config) as $table) {
        $registry->addTable($table);
    }

    $validation = (new SchemaValidator())->validate($registry);
    expect($validation->errors)->toBe([])
        ->and($validation->isValid())->toBeTrue();
});










