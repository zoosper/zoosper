<?php

declare(strict_types=1);

namespace Zoosper\Core\Tests\Unit\Schema;

use PDO;
use Zoosper\Core\Schema\SchemaForeignKey;
use Zoosper\Core\Schema\SchemaLoader;
use Zoosper\Core\Schema\SchemaRegistry;
use Zoosper\Core\Schema\SchemaSqlBuilder;
use Zoosper\Core\Schema\SchemaTable;
use Zoosper\Core\Schema\SchemaValidator;

function foreignKeyRegistry(string $action = SchemaForeignKey::ACTION_RESTRICT, bool $nullable = false): SchemaRegistry
{
    $registry = new SchemaRegistry();
    $registry->addTable(new SchemaTable('parents', [
        'id' => ['type' => 'integer', 'primary' => true, 'auto_increment' => true],
    ]));
    $registry->addTable(new SchemaTable('children', [
        'id' => ['type' => 'integer', 'primary' => true, 'auto_increment' => true],
        'parent_id' => ['type' => 'integer', 'nullable' => $nullable],
    ], foreignKeys: [
        'fk_children_parent' => new SchemaForeignKey(
            'fk_children_parent', ['parent_id'], 'parents', ['id'], $action,
        ),
    ]));

    return $registry;
}

it('loads typed foreign keys with restrictive defaults from module schema config', function (): void {
    $loader = (new \ReflectionClass(SchemaLoader::class))->newInstanceWithoutConstructor();
    $tables = $loader->tablesFromConfig(['tables' => [
        'parents' => ['columns' => ['id' => ['type' => 'integer', 'primary' => true]]],
        'children' => [
            'columns' => ['parent_id' => ['type' => 'integer']],
            'foreign_keys' => [
                'fk_children_parent' => [
                    'columns' => ['parent_id'],
                    'referenced_table' => 'parents',
                    'referenced_columns' => ['id'],
                ],
            ],
        ],
    ]]);

    expect($tables[1]->foreignKeys['fk_children_parent'])->toBeInstanceOf(SchemaForeignKey::class)
        ->and($tables[1]->foreignKeys['fk_children_parent']->onDelete)->toBe('RESTRICT')
        ->and($tables[1]->foreignKeys['fk_children_parent']->onUpdate)->toBe('RESTRICT');
});

it('emits equivalent named constraints for mysql and sqlite fresh tables', function (): void {
    $table = foreignKeyRegistry()->tables()['children'];
    foreach (['mysql', 'sqlite'] as $driver) {
        $sql = (new SchemaSqlBuilder($driver))->createTableSql($table);
        expect($sql)->toContain('CONSTRAINT fk_children_parent')
            ->toContain('FOREIGN KEY (parent_id)')
            ->toContain('REFERENCES parents (id)')
            ->toContain('ON DELETE RESTRICT')
            ->toContain('ON UPDATE RESTRICT');
    }
});

it('enforces restrict cascade and set-null semantics on fresh sqlite tables', function (): void {
    foreach ([SchemaForeignKey::ACTION_RESTRICT, SchemaForeignKey::ACTION_CASCADE, SchemaForeignKey::ACTION_SET_NULL] as $action) {
        $pdo = new PDO('sqlite::memory:');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->exec('PRAGMA foreign_keys = ON');
        $registry = foreignKeyRegistry($action, $action === SchemaForeignKey::ACTION_SET_NULL);
        $builder = new SchemaSqlBuilder('sqlite');
        foreach ($registry->tables() as $table) {
            $pdo->exec($builder->createTableSql($table));
        }
        $pdo->exec('INSERT INTO parents (id) VALUES (1)');
        $pdo->exec('INSERT INTO children (id,parent_id) VALUES (1,1)');

        if ($action === SchemaForeignKey::ACTION_RESTRICT) {
            expect(fn () => $pdo->exec('DELETE FROM parents WHERE id=1'))->toThrow(\PDOException::class);
        } else {
            $pdo->exec('DELETE FROM parents WHERE id=1');
            $value = $pdo->query('SELECT parent_id FROM children WHERE id=1')->fetchColumn();
            $action === SchemaForeignKey::ACTION_CASCADE
                ? expect($value)->toBeFalse()
                : expect($value)->toBeNull();
        }
    }
});

it('rejects missing tables columns and unsafe set-null definitions', function (): void {
    $missing = foreignKeyRegistry();
    $missing->addTable(new SchemaTable('broken', ['ghost_id' => ['type' => 'integer']], foreignKeys: [
        'fk_broken' => new SchemaForeignKey('fk_broken', ['ghost'], 'missing_table', ['id']),
    ]));
    expect((new SchemaValidator())->validate($missing)->isValid())->toBeFalse();

    expect((new SchemaValidator())->validate(foreignKeyRegistry(SchemaForeignKey::ACTION_SET_NULL, false))->isValid())->toBeFalse();
    expect(fn () => new SchemaForeignKey('fk', ['x'], 'parents', ['id'], 'DESTROY'))
        ->toThrow(\InvalidArgumentException::class, 'Unsupported foreign-key action');
});

it('merges module-owned foreign keys for the same declarative table', function (): void {
    $registry = new SchemaRegistry();
    $registry->addTable(new SchemaTable('children', ['id' => ['type' => 'integer']], foreignKeys: [
        'fk_one' => new SchemaForeignKey('fk_one', ['id'], 'parent_one', ['id']),
    ]));
    $registry->addTable(new SchemaTable('children', ['parent_two_id' => ['type' => 'integer']], foreignKeys: [
        'fk_two' => new SchemaForeignKey('fk_two', ['parent_two_id'], 'parent_two', ['id']),
    ]));
    expect($registry->tables()['children']->foreignKeys)->toHaveKeys(['fk_one', 'fk_two']);
});
