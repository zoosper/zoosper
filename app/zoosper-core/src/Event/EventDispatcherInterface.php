<?php

declare(strict_types=1);

namespace Zoosper\Core\Event;

interface EventDispatcherInterface
{
    public function listen(string $eventName, callable|EventListenerInterface $listener): self;

    public function dispatch(string $eventName, object $event): object;

    /** @return list<callable|EventListenerInterface> */
    public function listeners(string $eventName): array;
}
