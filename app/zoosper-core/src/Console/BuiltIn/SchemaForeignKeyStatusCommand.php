<?php

declare(strict_types=1);

namespace Zoosper\Core\Console\BuiltIn;

use JsonException;
use Zoosper\Core\Console\ConsoleCommandInterface;
use Zoosper\Core\Console\ConsoleOptions;
use Zoosper\Core\Console\ConsoleOutput;
use Zoosper\Database\Schema\SchemaForeignKeyReconciliation;
use Zoosper\Database\Schema\SchemaForeignKeyReconciliationService;

/** Read-only foreign-key reconciliation status and dry-run command. */
final readonly class SchemaForeignKeyStatusCommand implements ConsoleCommandInterface
{
    public function __construct(private SchemaForeignKeyReconciliationService $service)
    {
    }

    public function name(): string { return 'schema:foreign-keys:status'; }
    public function description(): string { return 'Inspect declared and live foreign-key reconciliation status.'; }

    /** @throws JsonException */
    public function run(array $args, ConsoleOutput $output): int
    {
        $format = ConsoleOptions::parse($args)['format'] ?? 'text';
        if (!in_array($format, ['text', 'json'], true)) {
            $output->errorln("Unsupported format '{$format}'. Expected text or json.");
            return 2;
        }

        $plans = $this->service->plan();
        $counts = $this->service->counts($plans);
        $blocked = ($counts[SchemaForeignKeyReconciliation::MISMATCH] ?? 0)
            + ($counts[SchemaForeignKeyReconciliation::SQLITE_REBUILD_REQUIRED] ?? 0) > 0;

        if ($format === 'json') {
            $output->writeln(json_encode([
                'blocked' => $blocked,
                'counts' => $counts,
                'constraints' => array_map(static fn (SchemaForeignKeyReconciliation $plan): array => [
                    'table' => $plan->table,
                    'name' => $plan->expected->name,
                    'columns' => $plan->expected->columns,
                    'referencedTable' => $plan->expected->referencedTable,
                    'referencedColumns' => $plan->expected->referencedColumns,
                    'onDelete' => $plan->expected->onDelete,
                    'onUpdate' => $plan->expected->onUpdate,
                    'status' => $plan->status,
                    'sql' => $plan->sql,
                    'diagnostic' => $plan->diagnostic,
                ], $plans),
            ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES));
            return $blocked ? 1 : 0;
        }

        $output->writeln('Foreign-key reconciliation status');
        $output->writeln('=================================');
        if ($plans === []) {
            $output->writeln('No declarative foreign keys are currently registered.');
        }
        foreach ($plans as $plan) {
            $output->writeln(sprintf('[%s] %s.%s (%s) -> %s (%s)', strtoupper($plan->status), $plan->table, implode(',', $plan->expected->columns), $plan->expected->name, $plan->expected->referencedTable . '.' . implode(',', $plan->expected->referencedColumns), $plan->expected->onDelete));
            if ($plan->sql !== null) { $output->writeln('  SQL: ' . $plan->sql); }
            if ($plan->diagnostic !== null) { $output->writeln('  Diagnostic: ' . $plan->diagnostic); }
        }
        $output->writeln(sprintf(
            'Totals: present=%d add=%d mismatch=%d sqlite_rebuild_required=%d',
            $counts[SchemaForeignKeyReconciliation::PRESENT],
            $counts[SchemaForeignKeyReconciliation::ADD],
            $counts[SchemaForeignKeyReconciliation::MISMATCH],
            $counts[SchemaForeignKeyReconciliation::SQLITE_REBUILD_REQUIRED],
        ));

        return $blocked ? 1 : 0;
    }
}










