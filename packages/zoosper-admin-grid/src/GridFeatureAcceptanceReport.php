<?php

declare(strict_types=1);

namespace Zoosper\AdminGrid;

/** Immutable close-out report for one Grid feature adoption. */
final readonly class GridFeatureAcceptanceReport
{
    /** @param list<string> $passed @param list<string> $failed */
    public function __construct(
        public string $gridKey,
        public array $passed,
        public array $failed,
    ) {
        if (trim($gridKey) === '') {
            throw new \InvalidArgumentException('Grid acceptance requires a grid key.');
        }
    }

    public function isComplete(): bool
    {
        return $this->failed === [];
    }
}
