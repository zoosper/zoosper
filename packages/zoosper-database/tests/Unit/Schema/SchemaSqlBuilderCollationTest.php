<?php

declare(strict_types=1);

use Zoosper\Database\Schema\SchemaSqlBuilder;
use Zoosper\Database\Schema\SchemaTable;

/**
 * CORRECTNESS REGRESSION TEST — proves SchemaSqlBuilder now pins an
 * explicit collation on every MySQL/MariaDB CREATE TABLE statement, rather
 * than relying on whichever collation the connected server happens to
 * default to (which has genuinely changed across MariaDB major versions).
 *
 * File placement: app/zoosper-core/tests/Unit/Schema/SchemaSqlBuilderCollationTest.php
 * — 5 levels up to repo root, matching other per-module tests.
 */
function schemaSqlBuilderCollationTestTable(): SchemaTable
{
    return new SchemaTable(
        name: 'example_table',
        columns: [
            'id' => ['type' => 'integer', 'primary' => true, 'auto_increment' => true],
            'name' => ['type' => 'string', 'length' => 190],
        ],
    );
}

it('pins an explicit COLLATE clause on MySQL CREATE TABLE statements', function (): void {
    $builder = new SchemaSqlBuilder('mysql');
    $sql = $builder->createTableSql(schemaSqlBuilderCollationTestTable());

    expect($sql)->toContain('ENGINE=InnoDB');
    expect($sql)->toContain('DEFAULT CHARSET=utf8mb4');
    expect($sql)->toContain('COLLATE=utf8mb4_unicode_ci');
});

it('places COLLATE immediately after CHARSET, forming valid MySQL DDL syntax', function (): void {
    $builder = new SchemaSqlBuilder('mysql');
    $sql = $builder->createTableSql(schemaSqlBuilderCollationTestTable());

    // Confirms the exact clause ordering MySQL/MariaDB expects, not just
    // that both substrings appear somewhere in the statement.
    expect($sql)->toContain('DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
});

it('does not add any collation clause for SQLite (unaffected, no regression)', function (): void {
    $builder = new SchemaSqlBuilder('sqlite');
    $sql = $builder->createTableSql(schemaSqlBuilderCollationTestTable());

    expect($sql)->not->toContain('COLLATE');
    expect($sql)->not->toContain('ENGINE');
    expect($sql)->not->toContain('CHARSET');
    expect($sql)->toContain('CREATE TABLE IF NOT EXISTS example_table');
});

it('still produces correct column definitions alongside the new collation clause (no regression)', function (): void {
    $builder = new SchemaSqlBuilder('mysql');
    $sql = $builder->createTableSql(schemaSqlBuilderCollationTestTable());

    expect($sql)->toContain('id INT AUTO_INCREMENT PRIMARY KEY');
    expect($sql)->toContain('name VARCHAR(190) NOT NULL');
});











