<?php

declare(strict_types=1);

namespace Zoosper\Core\Tests\Unit\Schema;

use Zoosper\Core\Schema\SchemaSqlBuilder;
use Zoosper\Core\Schema\SchemaTable;

function longtextTable(): SchemaTable
{
    return new SchemaTable(
        name: 'message_archive',
        columns: [
            'id' => ['type' => 'integer', 'primary' => true, 'auto_increment' => true],
            'body' => ['type' => 'longtext', 'nullable' => true],
        ],
        indexes: [],
    );
}

test('longtext maps to native LONGTEXT on mysql', function (): void {
    $sql = (new SchemaSqlBuilder('mysql'))->createTableSql(longtextTable());

    expect($sql)->toContain('body LONGTEXT NULL');
});

test('longtext maps to TEXT affinity on sqlite', function (): void {
    $sql = (new SchemaSqlBuilder('sqlite'))->createTableSql(longtextTable());

    expect($sql)->toContain('body TEXT NULL');
    expect($sql)->not->toContain('LONGTEXT');
});
