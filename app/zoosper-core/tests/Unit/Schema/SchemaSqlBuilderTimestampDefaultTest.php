<?php

declare(strict_types=1);

namespace Zoosper\Core\Tests\Unit\Schema;

use Zoosper\Core\Schema\SchemaSqlBuilder;
use Zoosper\Core\Schema\SchemaTable;

test('mysql datetime current timestamp defaults are emitted as expressions not quoted strings', function () {
    $sql = (new SchemaSqlBuilder('mysql'))->createTableSql(new SchemaTable(
        name: 'media_assets',
        columns: [
            'id' => ['type' => 'integer', 'primary' => true, 'auto_increment' => true],
            'created_at' => ['type' => 'datetime', 'nullable' => false, 'default' => 'CURRENT_TIMESTAMP'],
            'updated_at' => ['type' => 'datetime', 'nullable' => false, 'default' => 'CURRENT_TIMESTAMP'],
        ],
        indexes: [],
    ));

    expect($sql)->toContain('created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP');
    expect($sql)->toContain('updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP');
    expect($sql)->not->toContain("DEFAULT 'CURRENT_TIMESTAMP'");
});

test('sqlite current timestamp defaults are also emitted as expressions', function () {
    $sql = (new SchemaSqlBuilder('sqlite'))->createTableSql(new SchemaTable(
        name: 'media_assets',
        columns: [
            'id' => ['type' => 'integer', 'primary' => true, 'auto_increment' => true],
            'created_at' => ['type' => 'datetime', 'nullable' => false, 'default' => 'CURRENT_TIMESTAMP'],
        ],
        indexes: [],
    ));

    expect($sql)->toContain('created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP');
});
