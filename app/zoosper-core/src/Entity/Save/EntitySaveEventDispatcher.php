<?php

declare(strict_types=1);

namespace Zoosper\Core\Entity\Save;

/**
 * Lightweight in-process lifecycle event dispatcher for entity save flows.
 *
 * This is intentionally small and explicit. It gives Zoosper modules a stable
 * place to register listeners while the broader service-provider driven event
 * wiring evolves. Listeners are ordered by registration order.
 */
final class EntitySaveEventDispatcher implements EntitySaveEventDispatcherInterface
{
    /** @var array<string, list<EntitySaveEventListenerInterface|callable(EntitySaveContext): void>> */
    private array $listeners = [];

    public function listen(string $eventName, EntitySaveEventListenerInterface|callable $listener): self
    {
        $this->listeners[$eventName][] = $listener;

        return $this;
    }

    public function dispatch(string $eventName, EntitySaveContext $context): EntitySaveContext
    {
        foreach ($this->listeners[$eventName] ?? [] as $listener) {
            if ($listener instanceof EntitySaveEventListenerInterface) {
                $listener->handle($context);
                continue;
            }

            $listener($context);
        }

        return $context;
    }

    /** @return list<EntitySaveEventListenerInterface|callable(EntitySaveContext): void> */
    public function listeners(string $eventName): array
    {
        return $this->listeners[$eventName] ?? [];
    }
}
