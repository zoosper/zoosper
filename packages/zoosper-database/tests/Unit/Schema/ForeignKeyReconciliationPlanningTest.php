<?php

declare(strict_types=1);

namespace Zoosper\Database\Tests\Schema;

use PDO;
use Zoosper\Database\Schema\SchemaForeignKey;
use Zoosper\Database\Schema\SchemaForeignKeyInspector;
use Zoosper\Database\Schema\SchemaForeignKeyReconciliation;
use Zoosper\Database\Schema\SchemaForeignKeyReconciliationPlanner;
use Zoosper\Database\Schema\SchemaSqlBuilder;
use Zoosper\Database\Schema\SchemaTable;

function reconciliationTable(): SchemaTable
{
    return new SchemaTable('children', [
        'id' => ['type' => 'integer', 'primary' => true, 'auto_increment' => true],
        'parent_id' => ['type' => 'integer', 'nullable' => false],
    ], foreignKeys: [
        'fk_children_parent' => new SchemaForeignKey(
            'fk_children_parent', ['parent_id'], 'parents', ['id'], onDelete: 'CASCADE',
        ),
    ]);
}

function sqliteReconciliationPdo(bool $withForeignKey): PDO
{
    $pdo = new PDO('sqlite::memory:');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec('PRAGMA foreign_keys = ON');
    $pdo->exec('CREATE TABLE parents (id INTEGER PRIMARY KEY AUTOINCREMENT)');
    $constraint = $withForeignKey
        ? ', CONSTRAINT fk_children_parent FOREIGN KEY (parent_id) REFERENCES parents (id) ON DELETE CASCADE ON UPDATE RESTRICT'
        : '';
    $pdo->exec('CREATE TABLE children (id INTEGER PRIMARY KEY AUTOINCREMENT, parent_id INTEGER NOT NULL' . $constraint . ')');

    return $pdo;
}

it('normalises live sqlite foreign keys including actions and composite sequence', function (): void {
    $states = (new SchemaForeignKeyInspector(sqliteReconciliationPdo(true), 'sqlite'))->forTable('children');
    $state = array_values($states)[0];
    expect($state->columns)->toBe(['parent_id'])
        ->and($state->referencedTable)->toBe('parents')
        ->and($state->referencedColumns)->toBe(['id'])
        ->and($state->onDelete)->toBe('CASCADE')
        ->and($state->onUpdate)->toBe('RESTRICT');
});

it('treats an equivalent sqlite constraint as present even though pragma does not retain its declared name', function (): void {
    $plans = (new SchemaForeignKeyReconciliationPlanner(sqliteReconciliationPdo(true), 'sqlite'))->plan(reconciliationTable());
    expect($plans)->toHaveCount(1)
        ->and($plans[0]->status)->toBe(SchemaForeignKeyReconciliation::PRESENT)
        ->and($plans[0]->sql)->toBeNull();
});

it('returns an explicit rebuild-required diagnostic for a missing sqlite constraint', function (): void {
    $plans = (new SchemaForeignKeyReconciliationPlanner(sqliteReconciliationPdo(false), 'sqlite'))->plan(reconciliationTable());
    expect($plans[0]->status)->toBe(SchemaForeignKeyReconciliation::SQLITE_REBUILD_REQUIRED)
        ->and($plans[0]->diagnostic)->toContain('automatic rebuild is prohibited')
        ->and($plans[0]->sql)->toBeNull();
});

it('distinguishes a named mismatch from an equivalent constraint under another name', function (): void {
    $pdo = new PDO('sqlite::memory:');
    $pdo->exec('CREATE TABLE parents (id INTEGER PRIMARY KEY)');
    $pdo->exec('CREATE TABLE children (parent_id INTEGER, other_id INTEGER, FOREIGN KEY (parent_id) REFERENCES parents (id) ON DELETE CASCADE ON UPDATE RESTRICT, FOREIGN KEY (other_id) REFERENCES parents (id))');
    $states = (new SchemaForeignKeyInspector($pdo, 'sqlite'))->forTable('children');
    $mismatchedName = array_key_first(array_filter($states, fn ($state) => $state->columns === ['other_id']));
    $matchingName = array_key_first(array_filter($states, fn ($state) => $state->columns === ['parent_id']));

    $expected = new SchemaForeignKey($matchingName, ['parent_id'], 'parents', ['id'], onDelete: 'CASCADE');
    $table = new SchemaTable('children', ['parent_id' => [], 'other_id' => []], foreignKeys: [$matchingName => $expected]);
    $plans = (new SchemaForeignKeyReconciliationPlanner($pdo, 'sqlite'))->plan($table);
    expect($plans[0]->status)->toBe(SchemaForeignKeyReconciliation::PRESENT);

    $expected = new SchemaForeignKey($mismatchedName, ['parent_id'], 'parents', ['id'], onDelete: 'CASCADE');
    $table = new SchemaTable('children', ['parent_id' => [], 'other_id' => []], foreignKeys: [$mismatchedName => $expected]);
    $plans = (new SchemaForeignKeyReconciliationPlanner($pdo, 'sqlite'))->plan($table);
    expect($plans[0]->status)->toBe(SchemaForeignKeyReconciliation::PRESENT);

    $expected = new SchemaForeignKey($mismatchedName, ['parent_id'], 'parents', ['id'], onDelete: 'SET NULL');
    $table = new SchemaTable('children', ['parent_id' => [], 'other_id' => []], foreignKeys: [$mismatchedName => $expected]);
    $plans = (new SchemaForeignKeyReconciliationPlanner($pdo, 'sqlite'))->plan($table);
    expect($plans[0]->status)->toBe(SchemaForeignKeyReconciliation::MISMATCH)
        ->and($plans[0]->diagnostic)->toContain('expected name')
        ->and($plans[0]->sql)->toBeNull();
});

it('builds safe mysql alter-table SQL and refuses sqlite alteration', function (): void {
    $foreignKey = reconciliationTable()->foreignKeys['fk_children_parent'];
    $sql = (new SchemaSqlBuilder('mysql'))->addForeignKeySql('children', $foreignKey);
    expect($sql)->toBe('ALTER TABLE children ADD CONSTRAINT fk_children_parent FOREIGN KEY (parent_id) REFERENCES parents (id) ON DELETE CASCADE ON UPDATE RESTRICT')
        ->and(fn () => (new SchemaSqlBuilder('sqlite'))->addForeignKeySql('children', $foreignKey))
        ->toThrow(\RuntimeException::class, 'explicit table rebuild migration');
});

it('rejects unsafe table identifiers before database inspection', function (): void {
    expect(fn () => (new SchemaForeignKeyInspector(sqliteReconciliationPdo(false), 'sqlite'))->forTable('children;drop'))
        ->toThrow(\RuntimeException::class, 'Unsafe schema identifier');
});











