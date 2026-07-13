<?php

declare(strict_types=1);

namespace Zoosper\Core\Entity\Save;

/**
 * Dispatches lifecycle events for modular entity save operations.
 */
interface EntitySaveEventDispatcherInterface
{
    public function dispatch(string $eventName, EntitySaveContext $context): EntitySaveContext;
}
