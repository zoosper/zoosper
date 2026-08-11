<?php

declare(strict_types=1);

namespace Zoosper\Core\Entity\Lifecycle;

use InvalidArgumentException;

/** Stable, value-free identity passed to lifecycle policies. */
final readonly class EntityLifecycleSubject
{
    /** @param array<string, scalar|null> $context */
    public function __construct(
        public string $entityType,
        public string|int $entityId,
        public array $context = [],
    ) {
        if (trim($entityType) === '') {
            throw new InvalidArgumentException('Lifecycle entity type must not be empty.');
        }

        if ((string) $entityId === '') {
            throw new InvalidArgumentException('Lifecycle entity ID must not be empty.');
        }
    }
}
