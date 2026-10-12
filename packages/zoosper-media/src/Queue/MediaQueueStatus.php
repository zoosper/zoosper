<?php

declare(strict_types=1);

namespace Zoosper\Media\Queue;

/** Immutable, secret-free operational summary of the Media derivative queue. */
final readonly class MediaQueueStatus
{
    /**
     * @param array{pending: int, processing: int, completed: int, failed: int} $counts
     */
    public function __construct(
        public array $counts,
        public int $retryPending,
        public int $totalAttempts,
        public ?int $oldestPendingAgeSeconds,
        public ?int $averageQueueWaitSeconds,
        public ?int $maximumQueueWaitSeconds,
        public ?int $averageProcessingSeconds,
        public ?int $maximumProcessingSeconds,
    ) {
    }

    /**
     * @return array{
     *     counts: array{pending: int, processing: int, completed: int, failed: int},
     *     retryPending: int,
     *     totalAttempts: int,
     *     oldestPendingAgeSeconds: int|null,
     *     queueWaitSeconds: array{average: int|null, maximum: int|null},
     *     processingSeconds: array{average: int|null, maximum: int|null}
     * }
     */
    public function toArray(): array
    {
        return [
            'counts' => $this->counts,
            'retryPending' => $this->retryPending,
            'totalAttempts' => $this->totalAttempts,
            'oldestPendingAgeSeconds' => $this->oldestPendingAgeSeconds,
            'queueWaitSeconds' => [
                'average' => $this->averageQueueWaitSeconds,
                'maximum' => $this->maximumQueueWaitSeconds,
            ],
            'processingSeconds' => [
                'average' => $this->averageProcessingSeconds,
                'maximum' => $this->maximumProcessingSeconds,
            ],
        ];
    }
}
