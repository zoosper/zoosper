<?php

declare(strict_types=1);

namespace Zoosper\Admin\Console;

use Zoosper\Audit\AuditLogRepository;
use Zoosper\Audit\LoginHistoryRepository;
use Zoosper\Core\Console\ConsoleCommandInterface;
use Zoosper\Core\Console\ConsoleOptions;
use Zoosper\Core\Console\ConsoleOutput;

/**
 * `admin:logs:prune` — prunes admin audit log and login history entries older than a given number of days.
 */
final readonly class PruneLogsCommand implements ConsoleCommandInterface
{
    public function __construct(
        private AuditLogRepository $auditLogs,
        private LoginHistoryRepository $loginHistory,
    ) {
    }

    public function name(): string
    {
        return 'admin:logs:prune';
    }

    public function description(): string
    {
        return '--days=90';
    }

    public function run(array $args, ConsoleOutput $output): int
    {
        $options = ConsoleOptions::parse($args);
        $days = (int) ($options['days'] ?? 90);
        if ($days <= 0) {
            $output->errorln('Retention days must be a positive integer.');
            return 1;
        }

        $cutoff = gmdate('Y-m-d H:i:s', time() - ($days * 86400));
        $output->writeln("Pruning activity logs and login history older than {$cutoff} ({$days} days)...");

        $auditDeleted = $this->auditLogs->deleteOlderThan($cutoff);
        $loginDeleted = $this->loginHistory->deleteOlderThan($cutoff);

        $output->writeln("Pruned {$auditDeleted} activity log entry(s) and {$loginDeleted} login history entry(s).");

        return 0;
    }
}










