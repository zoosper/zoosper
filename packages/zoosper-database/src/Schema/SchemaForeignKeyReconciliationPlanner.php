<?php

declare(strict_types=1);

namespace Zoosper\Database\Schema;

use PDO;

/** Plans existing-table reconciliation without applying or rebuilding anything. */
final readonly class SchemaForeignKeyReconciliationPlanner
{
    private SchemaForeignKeyInspector $inspector;
    private SchemaSqlBuilder $sql;

    public function __construct(PDO $pdo, private string $driver)
    {
        $this->inspector = new SchemaForeignKeyInspector($pdo, $driver);
        $this->sql = new SchemaSqlBuilder($driver);
    }

    /** @return list<SchemaForeignKeyReconciliation> */
    public function plan(SchemaTable $table): array
    {
        $actual = $this->inspector->forTable($table->name);
        $plans = [];
        foreach ($table->foreignKeys as $expected) {
            $named = $actual[$expected->name] ?? null;
            if ($named?->matches($expected)) {
                $plans[] = new SchemaForeignKeyReconciliation($table->name, $expected, SchemaForeignKeyReconciliation::PRESENT);
                continue;
            }

            foreach ($actual as $state) {
                if ($state->matches($expected)) {
                    $plans[] = new SchemaForeignKeyReconciliation($table->name, $expected, SchemaForeignKeyReconciliation::PRESENT);
                    continue 2;
                }
            }

            if ($named !== null) {
                $plans[] = new SchemaForeignKeyReconciliation(
                    $table->name,
                    $expected,
                    SchemaForeignKeyReconciliation::MISMATCH,
                    diagnostic: 'A foreign key with the expected name exists but its columns, target, or actions differ. Manual reconciliation is required.',
                );
                continue;
            }

            if ($this->driver === 'sqlite') {
                $plans[] = new SchemaForeignKeyReconciliation(
                    $table->name,
                    $expected,
                    SchemaForeignKeyReconciliation::SQLITE_REBUILD_REQUIRED,
                    diagnostic: 'SQLite cannot add this constraint with ALTER TABLE. Create an explicit data-preserving rebuild migration; automatic rebuild is prohibited.',
                );
                continue;
            }

            $plans[] = new SchemaForeignKeyReconciliation(
                $table->name,
                $expected,
                SchemaForeignKeyReconciliation::ADD,
                $this->sql->addForeignKeySql($table->name, $expected),
            );
        }

        return $plans;
    }
}











