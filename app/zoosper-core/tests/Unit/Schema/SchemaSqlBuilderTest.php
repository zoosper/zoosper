<?php

declare(strict_types=1);

namespace Zoosper\Core\Tests\Unit\Schema;

/**
 * Regression tests locking in the declarative schema SQL generation.
 *
 * Phase 1.29 Step 1 - these tests pin the driver-correct SQL that
 * SchemaSqlBuilder produces (MySQL and SQLite) BEFORE the schema-engine
 * unification, so the risky SQL-generation logic cannot silently change.
 *
 * PCI-aware: the schema layer describes structure only, never secret values.
 */

use Zoosper\Core\Schema\SchemaSqlBuilder;
use Zoosper\Core\Schema\SchemaTable;

/**
 * A representative table exercising primary/auto-increment, string and json.
 */
function widgetsTable(): SchemaTable
{
    return new SchemaTable(
        name: 'widgets',
        columns: [
            'id' => ['type' => 'integer', 'primary' => true, 'auto_increment' => true],
            'name' => ['type' => 'string', 'length' => 190, 'nullable' => false],
            'payload' => ['type' => 'json', 'nullable' => true],
        ],
        indexes: [
            'idx_widgets_name' => ['columns' => ['name']],
        ],
    );
}

test('creates a mysql table with engine and auto-increment primary key', function () {
    $sql = (new SchemaSqlBuilder('mysql'))->createTableSql(widgetsTable());

    expect($sql)->toContain('CREATE TABLE IF NOT EXISTS widgets');
    expect($sql)->toContain('id INT AUTO_INCREMENT PRIMARY KEY');
    expect($sql)->toContain('name VARCHAR(190) NOT NULL');
    expect($sql)->toContain('ENGINE=InnoDB');
});

test('creates a sqlite table with autoincrement and no engine', function () {
    $sql = (new SchemaSqlBuilder('sqlite'))->createTableSql(widgetsTable());

    expect($sql)->toContain('id INTEGER PRIMARY KEY AUTOINCREMENT');
    expect($sql)->toContain('name TEXT NOT NULL');
    expect($sql)->not->toContain('ENGINE');
});

test('maps json to LONGTEXT on mysql and TEXT on sqlite', function () {
    $mysql = (new SchemaSqlBuilder('mysql'))->createTableSql(widgetsTable());
    $sqlite = (new SchemaSqlBuilder('sqlite'))->createTableSql(widgetsTable());

    expect($mysql)->toContain('payload LONGTEXT');
    expect($sqlite)->toContain('payload TEXT');
});

test('builds an add-column statement', function () {
    $sql = (new SchemaSqlBuilder('mysql'))->addColumnSql('pages', 'meta_title', [
        'type' => 'string',
        'length' => 255,
        'nullable' => true,
    ]);

    expect($sql)->toContain('ALTER TABLE pages ADD COLUMN meta_title VARCHAR(255)');
    expect($sql)->toContain('NULL');
});

test('builds a unique index', function () {
    $sql = (new SchemaSqlBuilder('mysql'))->createIndexSql('t', 'uniq_x', [
        'columns' => ['a', 'b'],
        'unique' => true,
    ]);

    expect($sql)->toBe('CREATE UNIQUE INDEX uniq_x ON t (a, b)');
});

test('builds a plain index', function () {
    $sql = (new SchemaSqlBuilder('mysql'))->createIndexSql('t', 'idx_x', [
        'columns' => ['a'],
    ]);

    expect($sql)->toBe('CREATE INDEX idx_x ON t (a)');
});
