<?php

declare(strict_types=1);

namespace Zoosper\AdminGrid;

/** Presentation state for the selected Grid view. */
final readonly class GridWorkspaceViewStatus
{
    public function __construct(
        public string $label,
        public bool $isSavedView,
        public bool $isDirty,
    ) {
        if (trim($this->label) === '') {
            throw new \InvalidArgumentException('Grid view status requires a label.');
        }
        if (!$this->isSavedView && $this->isDirty) {
            throw new \InvalidArgumentException(
                'The unsaved default workspace cannot be marked as a dirty saved view.',
            );
        }
    }
}











