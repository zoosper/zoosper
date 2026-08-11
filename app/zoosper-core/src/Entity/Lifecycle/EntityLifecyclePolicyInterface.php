<?php

declare(strict_types=1);

namespace Zoosper\Core\Entity\Lifecycle;

/** Entity-specific policy. Policies inspect only; executors mutate separately. */
interface EntityLifecyclePolicyInterface
{
    public function entityType(): string;

    public function decide(EntityLifecycleSubject $subject, EntityLifecycleOperation $operation): EntityLifecycleDecision;
}
