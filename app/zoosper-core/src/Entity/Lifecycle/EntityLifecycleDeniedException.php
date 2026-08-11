<?php

declare(strict_types=1);

namespace Zoosper\Core\Entity\Lifecycle;

use RuntimeException;

/** Descriptive failure carrying the complete denied decision. */
final class EntityLifecycleDeniedException extends RuntimeException
{
    public function __construct(public readonly EntityLifecycleDecision $decision)
    {
        $reasons = array_map(
            static fn (EntityLifecycleBlocker $blocker): string => $blocker->message,
            $decision->blockers,
        );

        parent::__construct(sprintf(
            'Cannot %s %s "%s": %s',
            $decision->operation->value,
            $decision->subject->entityType,
            (string) $decision->subject->entityId,
            implode(' ', $reasons),
        ));
    }
}
