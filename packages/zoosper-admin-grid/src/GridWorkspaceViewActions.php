<?php

declare(strict_types=1);

namespace Zoosper\AdminGrid;

/** View-action presentation state derived from the resolved workspace. */
final readonly class GridWorkspaceViewActions
{
    public function __construct(
        public string $viewName,
        public bool $hasActiveView,
        public bool $isDefault,
        public bool $isDirty,
        public ?int $bookmarkId,
    ) {
        if ($hasActiveView && ($bookmarkId === null || $bookmarkId <= 0)) {
            throw new \InvalidArgumentException('An active Grid view requires a bookmark ID.');
        }
        if (!$hasActiveView && $bookmarkId !== null) {
            throw new \InvalidArgumentException('Default workspace cannot carry a bookmark ID.');
        }
    }
}











