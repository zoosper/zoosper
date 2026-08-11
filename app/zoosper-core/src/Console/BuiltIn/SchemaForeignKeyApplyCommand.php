<?php

declare(strict_types=1);

namespace Zoosper\Core\Console\BuiltIn;

use RuntimeException;
use Zoosper\Core\Console\ConsoleCommandInterface;
use Zoosper\Core\Console\ConsoleOptions;
use Zoosper\Core\Console\ConsoleOutput;
use Zoosper\Core\Schema\SchemaForeignKeyReconciliationService;

/** Guarded MySQL-only apply command; dry-run delegates to the status command. */
final readonly class SchemaForeignKeyApplyCommand implements ConsoleCommandInterface
{
    public function __construct(
        private SchemaForeignKeyReconciliationService $service,
        private SchemaForeignKeyStatusCommand $status,
    ) {
    }

    public function name(): string { return 'schema:foreign-keys:apply'; }
    public function description(): string { return 'Apply only safe missing MySQL foreign keys; use --dry-run=1 to inspect.'; }

    public function run(array $args, ConsoleOutput $output): int
    {
        $options = ConsoleOptions::parse($args);
        if (($options['dry-run'] ?? '0') === '1') {
            return $this->status->run($args, $output);
        }
        if (($options['confirm'] ?? '') !== 'apply') {
            $output->errorln('Refusing foreign-key apply without --confirm=apply. Run schema:foreign-keys:status first.');
            return 2;
        }

        try {
            $statements = $this->service->apply();
        } catch (RuntimeException $exception) {
            $output->errorln($exception->getMessage());
            return 1;
        }
        if ($statements === []) {
            $output->writeln('No missing MySQL foreign keys required application.');
            return 0;
        }
        foreach ($statements as $sql) {
            $output->writeln('[APPLIED] ' . $sql);
        }
        $output->writeln('Applied and snapshot-recorded ' . count($statements) . ' foreign-key statement(s).');
        return 0;
    }
}
