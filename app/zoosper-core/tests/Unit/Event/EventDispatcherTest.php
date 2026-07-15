<?php

declare(strict_types=1);

namespace Zoosper\Core\Tests\Unit\Event;

use RuntimeException;
use Zoosper\Core\Event\EventDispatcher;
use Zoosper\Core\Event\EventListenerInterface;
use Zoosper\Core\Event\GenericEvent;

test('dispatches listeners in registration order', function () {
    $dispatcher = new EventDispatcher();
    $calls = [];

    $dispatcher->listen('demo.event', function () use (&$calls): void { $calls[] = 'first'; });
    $dispatcher->listen('demo.event', function () use (&$calls): void { $calls[] = 'second'; });

    $event = new GenericEvent('demo.event', ['ok' => true]);
    expect($dispatcher->dispatch('demo.event', $event))->toBe($event);
    expect($calls)->toBe(['first', 'second']);
});

test('dispatches object listeners implementing the interface', function () {
    $dispatcher = new EventDispatcher();
    $listener = new class implements EventListenerInterface {
        public bool $handled = false;

        public function handle(object $event): void
        {
            $this->handled = true;
        }
    };

    $dispatcher->listen('demo.event', $listener);
    $dispatcher->dispatch('demo.event', new GenericEvent('demo.event'));

    expect($listener->handled)->toBeTrue();
});

test('a throwing listener does not stop later listeners', function () {
    $dispatcher = new EventDispatcher();
    $called = false;

    $dispatcher->listen('demo.event', function (): void { throw new RuntimeException('broken observer'); });
    $dispatcher->listen('demo.event', function () use (&$called): void { $called = true; });

    $dispatcher->dispatch('demo.event', new GenericEvent('demo.event'));

    expect($called)->toBeTrue();
});
