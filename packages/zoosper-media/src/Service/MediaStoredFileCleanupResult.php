<?php

declare(strict_types=1);

namespace Zoosper\Media\Service;

/**
 * Result of an orphan-file cleanup attempt.
 */
final readonly class MediaStoredFileCleanupResult
{
    /**
     * @param list<string> $deleted
     * @param list<string> $skipped
     */
    public function __construct(
        public array $deleted,
        public array $skipped,
    ) {
    }

    public function deletedCount(): int
    {
        return count($this->deleted);
    }

    public function skippedCount(): int
    {
        return count($this->skipped);
    }
}
