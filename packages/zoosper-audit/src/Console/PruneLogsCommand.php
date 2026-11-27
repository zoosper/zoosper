<?php

declare(strict_types=1);

namespace Zoosper\Audit\Console;

use Zoosper\Audit\AuditLogRepository;
use Zoosper\Audit\LoginHistoryRepository;
use Zoosper\Core\Console\ConsoleCommandInterface;
use Zoosper\Core\Console\ConsoleOptions;
use Zoosper\Core\Console\ConsoleOutput;

/**
 * `admin:logs:prune` — prunes admin audit log and login history entries older than a given number of days.
 */
/**
 * Declarative runtime entry point discovered through module configuration.
 *
 * @psalm-api
 */
final readonly class PruneLogsCommand implements ConsoleCommandInterface
{
    public function __construct(
        private AuditLogRepository $auditLogs,
        private LoginHistoryRepository $loginHistory,
    ) {
    }

    #[\Override]
    public function name(): string
    {
        return 'admin:logs:prune';
    }

    #[\Override]
    public function description(): string
    {
        return '--days=90';
    }

    #[\Override]
    public function run(array $args, ConsoleOutput $output): int
    {
        $options = ConsoleOptions::parse($args);
        $rawDays = $options['days'] ?? '90';
        $now = time();
        $maxDays = intdiv($now + 62135596800, 86400);
        $days = filter_var($rawDays, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => $maxDays]]);
        if (in_array('--days', $args, true) || !ctype_digit($rawDays) || $days === false) {
            $output->errorln('Retention days must be a positive integer.');
            return 1;
        }

        $cutoff = gmdate('Y-m-d H:i:s', $now - ($days * 86400));
        $output->writeln("Pruning activity logs and login history older than {$cutoff} ({$days} days)...");

        $auditDeleted = $this->auditLogs->deleteOlderThan($cutoff);
        $loginDeleted = $this->loginHistory->deleteOlderThan($cutoff);

        $output->writeln("Pruned {$auditDeleted} activity log entry(s) and {$loginDeleted} login history entry(s).");

        return 0;
    }
}











