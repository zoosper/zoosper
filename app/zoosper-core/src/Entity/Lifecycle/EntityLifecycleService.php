<?php

declare(strict_types=1);

namespace Zoosper\Core\Entity\Lifecycle;

/** Shared read-only policy boundary used before any lifecycle mutation. */
final readonly class EntityLifecycleService
{
    public function __construct(private EntityLifecyclePolicyRegistry $policies)
    {
    }

    public function decide(EntityLifecycleSubject $subject, EntityLifecycleOperation $operation): EntityLifecycleDecision
    {
        $decision = $this->policies->get($subject->entityType)->decide($subject, $operation);

        if ($decision->subject != $subject || $decision->operation !== $operation) {
            throw new \LogicException('Lifecycle policy returned a decision for a different subject or operation.');
        }

        return $decision;
    }

    /** @throws EntityLifecycleDeniedException */
    public function requireAllowed(EntityLifecycleSubject $subject, EntityLifecycleOperation $operation): EntityLifecycleDecision
    {
        $decision = $this->decide($subject, $operation);
        if (!$decision->isAllowed()) {
            throw new EntityLifecycleDeniedException($decision);
        }

        return $decision;
    }
}
