<?php

declare(strict_types=1);

namespace Zoosper\Core\Entity\Save;

/**
 * Listener contract for entity save lifecycle events.
 *
 * Modules can implement this interface to validate, mutate or react to entity
 * save data without changing core controllers. Listener implementations should
 * keep side effects appropriate to the lifecycle stage they subscribe to.
 */
interface EntitySaveEventListenerInterface
{
    public function handle(EntitySaveContext $context): void;
}
