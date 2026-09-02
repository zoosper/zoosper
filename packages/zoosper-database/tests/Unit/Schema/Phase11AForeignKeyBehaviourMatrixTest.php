<?php
declare(strict_types=1);

use Zoosper\Database\Schema\SchemaForeignKey;
use Zoosper\Database\Schema\SchemaRegistry;
use Zoosper\Database\Schema\SchemaSqlBuilder;
use Zoosper\Database\Schema\SchemaTable;

function phase11aBehaviourPdo(string $onDelete, string $onUpdate = SchemaForeignKey::ACTION_RESTRICT): PDO
{
    $pdo = new PDO('sqlite::memory:');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec('PRAGMA foreign_keys = ON');
    $registry = new SchemaRegistry();
    $registry->addTable(new SchemaTable('parents', [
        'id' => ['type' => 'integer', 'primary' => true],
    ]));
    $registry->addTable(new SchemaTable('children', [
        'id' => ['type' => 'integer', 'primary' => true],
        'parent_id' => ['type' => 'integer', 'nullable' => $onDelete === SchemaForeignKey::ACTION_SET_NULL],
    ], foreignKeys: [
        'fk_children_parent' => new SchemaForeignKey(
            'fk_children_parent',
            ['parent_id'],
            'parents',
            ['id'],
            $onDelete,
            $onUpdate,
        ),
    ]));
    $builder = new SchemaSqlBuilder('sqlite');
    foreach ($registry->tables() as $table) {
        $pdo->exec($builder->createTableSql($table));
    }
    return $pdo;
}

it('rejects orphan inserts with foreign-key enforcement enabled', function (): void {
    $pdo = phase11aBehaviourPdo(SchemaForeignKey::ACTION_CASCADE);
    expect(fn () => $pdo->exec('INSERT INTO children (id, parent_id) VALUES (1, 999)'))
        ->toThrow(PDOException::class);
});

it('cascades child deletion when its parent is deleted', function (): void {
    $pdo = phase11aBehaviourPdo(SchemaForeignKey::ACTION_CASCADE);
    $pdo->exec('INSERT INTO parents (id) VALUES (1)');
    $pdo->exec('INSERT INTO children (id, parent_id) VALUES (1, 1)');
    $pdo->exec('DELETE FROM parents WHERE id = 1');
    expect((int) $pdo->query('SELECT COUNT(*) FROM children')->fetchColumn())->toBe(0);
});

it('sets nullable child ownership to null when its parent is deleted', function (): void {
    $pdo = phase11aBehaviourPdo(SchemaForeignKey::ACTION_SET_NULL);
    $pdo->exec('INSERT INTO parents (id) VALUES (1)');
    $pdo->exec('INSERT INTO children (id, parent_id) VALUES (1, 1)');
    $pdo->exec('DELETE FROM parents WHERE id = 1');
    expect($pdo->query('SELECT parent_id FROM children WHERE id = 1')->fetchColumn())->toBeNull();
});

it('restricts parent key updates while a child still references the key', function (): void {
    $pdo = phase11aBehaviourPdo(SchemaForeignKey::ACTION_CASCADE);
    $pdo->exec('INSERT INTO parents (id) VALUES (1)');
    $pdo->exec('INSERT INTO children (id, parent_id) VALUES (1, 1)');
    expect(fn () => $pdo->exec('UPDATE parents SET id = 2 WHERE id = 1'))
        ->toThrow(PDOException::class);
});

it('reports no integrity violations after valid representative mutations', function (): void {
    $pdo = phase11aBehaviourPdo(SchemaForeignKey::ACTION_SET_NULL);
    $pdo->exec('INSERT INTO parents (id) VALUES (1)');
    $pdo->exec('INSERT INTO children (id, parent_id) VALUES (1, 1)');
    $pdo->exec('DELETE FROM parents WHERE id = 1');
    expect($pdo->query('PRAGMA foreign_key_check')->fetchAll(PDO::FETCH_ASSOC))->toBe([]);
});
