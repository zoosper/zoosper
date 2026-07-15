<?php

declare(strict_types=1);

namespace Zoosper\Core\Tests\Unit\Event;

use Zoosper\Core\Container\ServiceContainer;
use Zoosper\Core\Event\EventDispatcher;
use Zoosper\Core\Event\EventListenerInterface;
use Zoosper\Core\Event\GenericEvent;
use Zoosper\Core\Event\ModuleEventListenerLoader;
use Zoosper\Core\Exception\ZoosperException;

function makeEventLoader(ServiceContainer $services): ModuleEventListenerLoader
{
    $ref = new \ReflectionClass(ModuleEventListenerLoader::class);
    $loader = $ref->newInstanceWithoutConstructor();
    $ref->getProperty('services')->setValue($loader, $services);

    return $loader;
}

test('attaches closure listeners from config', function () {
    $dispatcher = new EventDispatcher();
    $loader = makeEventLoader(new ServiceContainer());
    $called = false;

    $loader->attachFromConfig($dispatcher, [
        'demo.event' => [function () use (&$called): void { $called = true; }],
    ]);

    $dispatcher->dispatch('demo.event', new GenericEvent('demo.event'));

    expect($called)->toBeTrue();
});

test('resolves class-string listeners from the container', function () {
    $services = new ServiceContainer();
    $listener = new class implements EventListenerInterface {
        public bool $handled = false;

        public function handle(object $event): void
        {
            $this->handled = true;
        }
    };
    $services->set($listener::class, $listener);

    $dispatcher = new EventDispatcher();
    makeEventLoader($services)->attachFromConfig($dispatcher, ['demo.event' => [$listener::class]]);
    $dispatcher->dispatch('demo.event', new GenericEvent('demo.event'));

    expect($listener->handled)->toBeTrue();
});

test('throws a descriptive error for an invalid listener entry', function () {
    $dispatcher = new EventDispatcher();
    $loader = makeEventLoader(new ServiceContainer());

    expect(fn () => $loader->attachFromConfig($dispatcher, ['demo.event' => [123]]))
        ->toThrow(ZoosperException::class);
});

test('throws when listener list is not iterable', function () {
    $dispatcher = new EventDispatcher();
    $loader = makeEventLoader(new ServiceContainer());

    expect(fn () => $loader->attachFromConfig($dispatcher, ['demo.event' => 'nope']))
        ->toThrow(ZoosperException::class);
});
