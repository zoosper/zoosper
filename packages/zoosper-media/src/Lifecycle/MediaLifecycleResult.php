<?php

declare(strict_types=1);

namespace Zoosper\Media\Lifecycle;

final readonly class MediaLifecycleResult
{
    /** @param array<string, int> $blockers */
    public function __construct(
        public bool $successful,
        public string $operation,
        public int $mediaId,
        public string $previousStatus,
        public ?string $newStatus = null,
        public array $blockers = [],
        public ?string $message = null,
    ) {
    }
}
