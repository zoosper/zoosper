<?php

declare(strict_types=1);

namespace Zoosper\Core\Console;

use Zoosper\Core\Security\RateLimit\RateLimitStoreMaintenanceInterface;

/**
 * Prunes expired database-backed fixed-window rate-limit buckets.
 *
 * @psalm-api Discovered through the Core module's config/console.php manifest.
 */
final readonly class PruneRateLimitBucketsCommand implements ConsoleCommandInterface
{
    public function __construct(private RateLimitStoreMaintenanceInterface $maintenance)
    {
    }

    #[\Override]
    public function name(): string
    {
        return 'rate-limit:prune';
    }

    #[\Override]
    public function description(): string
    {
        return 'Delete expired database rate-limit buckets.';
    }

    #[\Override]
    public function run(array $args, ConsoleOutput $output): int
    {
        if ($args !== []) {
            $output->errorln('rate-limit:prune does not accept arguments.');
            return 1;
        }

        $deleted = $this->maintenance->deleteExpired(time());
        $output->writeln("Pruned {$deleted} expired rate-limit bucket(s).");

        return 0;
    }
}
