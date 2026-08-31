<?php

declare(strict_types=1);

namespace Zoosper\AdminGrid;

/** Compares the current resolved workspace with its active saved view. */
final readonly class GridWorkspaceDirtyState
{
    public function __construct(private GridWorkspaceStateFingerprint $fingerprint)
    {
    }

    /** @param array<string, mixed>|null $savedState */
    public function isDirty(GridViewState $current, ?array $savedState): bool
    {
        if ($savedState === null) {
            return false;
        }

        return $this->fingerprint->fromViewState($current)
            !== $this->fingerprint->fromArray($savedState);
    }
}











