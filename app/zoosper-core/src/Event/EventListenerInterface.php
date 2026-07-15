<?php

declare(strict_types=1);

namespace Zoosper\Core\Event;

/**
 * Handles a general application event.
 *
 * General events are fire-and-forget notifications. Observers may react to an
 * event, but they must not abort the originating action. Use the entity-save
 * lifecycle when validation/mutation/abort semantics are needed.
 */
interface EventListenerInterface
{
    public function handle(object $event): void;
}
