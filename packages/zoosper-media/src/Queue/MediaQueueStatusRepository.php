<?php

declare(strict_types=1);

namespace Zoosper\Media\Queue;

use DateTimeImmutable;
use DateTimeZone;
use PDO;

/**
 * Read-only queue telemetry derived from persisted lifecycle timestamps.
 *
 * @psalm-api Resolved through the module service manifest.
 */
final readonly class MediaQueueStatusRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function inspect(?DateTimeImmutable $now = null): MediaQueueStatus
    {
        $counts = ['pending' => 0, 'processing' => 0, 'completed' => 0, 'failed' => 0];
        $statement = $this->pdo->query('SELECT status, COUNT(*) AS total FROM media_processing_queue GROUP BY status');
        foreach ($statement->fetchAll(PDO::FETCH_ASSOC) ?: [] as $row) {
            $status = (string) ($row['status'] ?? '');
            if (array_key_exists($status, $counts)) {
                $counts[$status] = (int) $row['total'];
            }
        }

        $summary = $this->pdo->query(
            "SELECT COALESCE(SUM(attempts), 0) AS total_attempts,
                    COALESCE(SUM(CASE WHEN status = 'pending' AND attempts > 0 THEN 1 ELSE 0 END), 0) AS retry_pending
             FROM media_processing_queue"
        )->fetch(PDO::FETCH_ASSOC);
        if ($summary === false) {
            $summary = [];
        }

        $clock = $now ?? new DateTimeImmutable('now', new DateTimeZone('UTC'));
        $oldest = $this->pdo->query("SELECT MIN(created_at) FROM media_processing_queue WHERE status = 'pending'")->fetchColumn();
        $oldestAge = is_string($oldest) && $oldest !== ''
            ? max(0, $clock->getTimestamp() - (new DateTimeImmutable($oldest, new DateTimeZone('UTC')))->getTimestamp())
            : null;

        $queueWaits = $this->durations('created_at', 'started_at', "started_at IS NOT NULL");
        $processing = $this->durations('started_at', 'completed_at', "completed_at IS NOT NULL AND started_at IS NOT NULL");

        return new MediaQueueStatus(
            $counts,
            (int) ($summary['retry_pending'] ?? 0),
            (int) ($summary['total_attempts'] ?? 0),
            $oldestAge,
            $queueWaits['average'],
            $queueWaits['maximum'],
            $processing['average'],
            $processing['maximum'],
        );
    }

    /** @return array{average: ?int, maximum: ?int} */
    private function durations(string $startColumn, string $endColumn, string $where): array
    {
        $rows = $this->pdo->query(
            "SELECT {$startColumn} AS started, {$endColumn} AS ended FROM media_processing_queue WHERE {$where}"
        )->fetchAll(PDO::FETCH_ASSOC) ?: [];
        $values = [];
        foreach ($rows as $row) {
            $started = new DateTimeImmutable((string) $row['started'], new DateTimeZone('UTC'));
            $ended = new DateTimeImmutable((string) $row['ended'], new DateTimeZone('UTC'));
            $values[] = max(0, $ended->getTimestamp() - $started->getTimestamp());
        }
        if ($values === []) {
            return ['average' => null, 'maximum' => null];
        }
        return ['average' => (int) round(array_sum($values) / count($values)), 'maximum' => max($values)];
    }
}
