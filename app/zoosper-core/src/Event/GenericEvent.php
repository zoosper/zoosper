<?php

declare(strict_types=1);

namespace Zoosper\Core\Event;

/**
 * Generic event payload for module-defined events that do not need a dedicated
 * typed event class yet.
 */
final readonly class GenericEvent
{
    /** @param array<string, mixed> $context */
    public function __construct(public string $name, public array $context = [])
    {
    }
}
