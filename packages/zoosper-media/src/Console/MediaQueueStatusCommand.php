<?php

declare(strict_types=1);

namespace Zoosper\Media\Console;

use JsonException;
use Zoosper\Core\Console\ConsoleCommandInterface;
use Zoosper\Core\Console\ConsoleOptions;
use Zoosper\Core\Console\ConsoleOutput;
use Zoosper\Media\Queue\MediaQueueStatusRepository;

/**
 * Read-only operator telemetry for Media queue depth, retries, failures and latency.
 *
 * @psalm-api Registered through the module console manifest.
 */
final readonly class MediaQueueStatusCommand implements ConsoleCommandInterface
{
    public function __construct(private MediaQueueStatusRepository $status)
    {
    }

    #[\Override]
    public function name(): string { return 'media:queue:status'; }

    #[\Override]
    public function description(): string { return 'Inspect Media queue depth, retries, failures and latency.'; }

    /** @throws JsonException */
    #[\Override]
    public function run(array $args, ConsoleOutput $output): int
    {
        $format = ConsoleOptions::parse($args)['format'] ?? 'text';
        if (!in_array($format, ['text', 'json'], true)) {
            $output->errorln("Unsupported format '{$format}'. Expected text or json.");
            return 2;
        }
        $status = $this->status->inspect();
        if ($format === 'json') {
            $output->writeln(json_encode($status->toArray(), JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES));
            return 0;
        }
        $counts = $status->counts;
        $output->writeln('Media queue status');
        $output->writeln('==================');
        $output->writeln(sprintf('Depth: pending=%d processing=%d completed=%d failed=%d', $counts['pending'], $counts['processing'], $counts['completed'], $counts['failed']));
        $output->writeln(sprintf('Retries: pending_after_failure=%d total_attempts=%d', $status->retryPending, $status->totalAttempts));
        $output->writeln('Oldest pending age: ' . self::seconds($status->oldestPendingAgeSeconds));
        $output->writeln(sprintf('Queue wait: average=%s maximum=%s', self::seconds($status->averageQueueWaitSeconds), self::seconds($status->maximumQueueWaitSeconds)));
        $output->writeln(sprintf('Processing latency: average=%s maximum=%s', self::seconds($status->averageProcessingSeconds), self::seconds($status->maximumProcessingSeconds)));
        return 0;
    }

    private static function seconds(?int $seconds): string
    {
        return $seconds === null ? 'n/a' : $seconds . 's';
    }
}
