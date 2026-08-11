<?php

declare(strict_types=1);

namespace Zoosper\Core\Schema;

use PDO;
use RuntimeException;
use Throwable;

/** Explicit operational boundary for planning and guarded MySQL foreign-key additions. */
final readonly class SchemaForeignKeyReconciliationService
{
    public function __construct(
        private PDO $pdo,
        private string $driver,
        private SchemaLoader $loader,
        private ?SchemaSnapshotRepository $snapshots = null,
    ) {
    }

    /** @return list<SchemaForeignKeyReconciliation> */
    public function plan(): array
    {
        $planner = new SchemaForeignKeyReconciliationPlanner($this->pdo, $this->driver);
        $plans = [];
        foreach ($this->loader->load()->tables() as $table) {
            array_push($plans, ...$planner->plan($table));
        }

        return $plans;
    }

    /** @return array<string, int> */
    public function counts(array $plans): array
    {
        $counts = [
            SchemaForeignKeyReconciliation::PRESENT => 0,
            SchemaForeignKeyReconciliation::ADD => 0,
            SchemaForeignKeyReconciliation::MISMATCH => 0,
            SchemaForeignKeyReconciliation::SQLITE_REBUILD_REQUIRED => 0,
        ];
        foreach ($plans as $plan) {
            $counts[$plan->status] = ($counts[$plan->status] ?? 0) + 1;
        }

        return $counts;
    }

    /** @return list<string> applied SQL statements */
    public function apply(): array
    {
        $plans = $this->plan();
        foreach ($plans as $plan) {
            if (in_array($plan->status, [SchemaForeignKeyReconciliation::MISMATCH, SchemaForeignKeyReconciliation::SQLITE_REBUILD_REQUIRED], true)) {
                throw new RuntimeException(sprintf(
                    'Foreign-key apply is blocked by %s on %s.%s: %s',
                    $plan->status,
                    $plan->table,
                    $plan->expected->name,
                    $plan->diagnostic ?? 'Manual reconciliation is required.',
                ));
            }
        }

        $statements = array_values(array_map(
            static fn (SchemaForeignKeyReconciliation $plan): string => (string) $plan->sql,
            array_filter($plans, static fn (SchemaForeignKeyReconciliation $plan): bool => $plan->status === SchemaForeignKeyReconciliation::ADD),
        ));
        if ($statements === []) {
            return [];
        }
        if ($this->driver !== 'mysql') {
            throw new RuntimeException('Automatic existing-table foreign-key apply is supported only for MySQL. SQLite requires explicit data-preserving rebuild migrations.');
        }

        $applied = [];
        try {
            foreach ($statements as $sql) {
                $this->pdo->exec($sql);
                $applied[] = $sql;
            }
        } catch (Throwable $exception) {
            if ($applied !== [] && $this->snapshots !== null) {
                $this->snapshots->record($applied);
            }
            throw new RuntimeException(
                sprintf('Foreign-key apply stopped after %d successful statement(s): %s', count($applied), $exception->getMessage()),
                previous: $exception,
            );
        }

        if ($this->snapshots !== null) {
            $this->snapshots->record($applied);
        }

        return $applied;
    }
}
