<?php

declare(strict_types=1);

namespace Zoosper\Core\Entity\Lifecycle;

/** Immutable policy result for one subject and requested operation. */
final readonly class EntityLifecycleDecision
{
    /** @param list<EntityLifecycleBlocker> $blockers */
    private function __construct(
        public EntityLifecycleSubject $subject,
        public EntityLifecycleOperation $operation,
        public array $blockers,
    ) {
    }

    public static function allow(EntityLifecycleSubject $subject, EntityLifecycleOperation $operation): self
    {
        return new self($subject, $operation, []);
    }

    /** @param list<EntityLifecycleBlocker> $blockers */
    public static function deny(EntityLifecycleSubject $subject, EntityLifecycleOperation $operation, array $blockers): self
    {
        if ($blockers === []) {
            throw new \InvalidArgumentException('A denied lifecycle decision requires at least one blocker.');
        }

        foreach ($blockers as $blocker) {
            if (!$blocker instanceof EntityLifecycleBlocker) {
                throw new \InvalidArgumentException('Lifecycle decision blockers must be EntityLifecycleBlocker instances.');
            }
        }

        return new self($subject, $operation, array_values($blockers));
    }

    public function isAllowed(): bool
    {
        return $this->blockers === [];
    }
}










