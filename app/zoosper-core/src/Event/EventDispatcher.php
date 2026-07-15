<?php

declare(strict_types=1);

namespace Zoosper\Core\Event;

use Throwable;
use Zoosper\Core\Log\ErrorHandler;

/**
 * Synchronous, in-process event dispatcher for general application events.
 *
 * Unlike the entity-save lifecycle, general events are observe/react
 * notifications. A listener must never be able to abort the originating action,
 * so each listener is isolated: exceptions are logged and dispatch continues.
 */
final class EventDispatcher implements EventDispatcherInterface
{
    /** @var array<string, list<callable|EventListenerInterface>> */
    private array $listeners = [];

    public function __construct(private readonly ?ErrorHandler $errorHandler = null)
    {
    }

    public function listen(string $eventName, callable|EventListenerInterface $listener): self
    {
        $this->listeners[$eventName] ??= [];
        $this->listeners[$eventName][] = $listener;

        return $this;
    }

    public function dispatch(string $eventName, object $event): object
    {
        foreach ($this->listeners[$eventName] ?? [] as $listener) {
            try {
                if ($listener instanceof EventListenerInterface) {
                    $listener->handle($event);
                    continue;
                }

                $listener($event);
            } catch (Throwable $exception) {
                $this->errorHandler?->logException($exception, [
                    'event_name' => $eventName,
                    'listener' => $this->listenerLabel($listener),
                ]);
            }
        }

        return $event;
    }

    /** @return list<callable|EventListenerInterface> */
    public function listeners(string $eventName): array
    {
        return $this->listeners[$eventName] ?? [];
    }

    private function listenerLabel(callable|EventListenerInterface $listener): string
    {
        if ($listener instanceof EventListenerInterface) {
            return $listener::class;
        }

        if ($listener instanceof \Closure) {
            return 'closure';
        }

        if (is_array($listener)) {
            $target = is_object($listener[0] ?? null) ? $listener[0]::class : (string) ($listener[0] ?? 'unknown');
            return $target . '::' . (string) ($listener[1] ?? 'unknown');
        }

        if (is_string($listener)) {
            return $listener;
        }

        return 'callable';
    }
}
