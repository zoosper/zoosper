<?php

declare(strict_types=1);

namespace Zoosper\Core\Tests\Unit\Entity\Save;

/**
 * Regression tests for ModuleEntitySaveListenerLoader.
 *
 * Phase 1.28. Module discovery (attach()) walks the module registry; the pure,
 * testable core is attachFromConfig(), which turns a config array into attached
 * listeners. These tests exercise attachFromConfig directly and assert the
 * descriptive-exception behaviour on misconfiguration.
 *
 * The loader is built via reflection without its constructor so the tests stay
 * independent of ModuleRegistry internals - only the ServiceContainer dependency
 * (used for class-string resolution) is injected.
 *
 * PCI-aware: no secrets/tokens are used in these tests.
 */

use Zoosper\Core\Container\ServiceContainer;
use Zoosper\Core\Entity\Save\EntityDataObject;
use Zoosper\Core\Entity\Save\EntitySaveContext;
use Zoosper\Core\Entity\Save\EntitySaveEventDispatcher;
use Zoosper\Core\Entity\Save\EntitySaveEventListenerInterface;
use Zoosper\Core\Entity\Save\EntitySaveLifecycle;
use Zoosper\Core\Entity\Save\FieldDefinitionRegistry;
use Zoosper\Core\Entity\Save\ModuleEntitySaveListenerLoader;
use Zoosper\Core\Exception\ZoosperException;

/**
 * Build a loader with the given container, bypassing the constructor so we do not
 * need a real ModuleRegistry (attachFromConfig does not use it).
 *
 * Note: ReflectionProperty::setAccessible() is not called - it is a no-op since
 * PHP 8.1 and deprecated in PHP 8.5. setValue() works without it.
 */
function makeListenerLoader(ServiceContainer $services): ModuleEntitySaveListenerLoader
{
    $ref = new \ReflectionClass(ModuleEntitySaveListenerLoader::class);
    $loader = $ref->newInstanceWithoutConstructor();

    $ref->getProperty('services')->setValue($loader, $services);

    return $loader;
}

/**
 * Build a minimal page save context for dispatch assertions.
 */
function makeDiscoveryContext(): EntitySaveContext
{
    return new EntitySaveContext('page', new EntityDataObject(), new FieldDefinitionRegistry());
}

test('attaches a closure listener to an event', function () {
    // Arrange
    $dispatcher = new EntitySaveEventDispatcher();
    $loader = makeListenerLoader(new ServiceContainer());
    $called = 0;

    // Act
    $loader->attachFromConfig($dispatcher, [
        EntitySaveLifecycle::VALIDATE_AFTER => [
            function () use (&$called): void {
                $called++;
            },
        ],
    ]);

    // Assert
    expect($dispatcher->listeners(EntitySaveLifecycle::VALIDATE_AFTER))->toHaveCount(1);
    $dispatcher->dispatch(EntitySaveLifecycle::VALIDATE_AFTER, makeDiscoveryContext());
    expect($called)->toBe(1);
});

test('attaches an object listener implementing the interface', function () {
    // Arrange
    $dispatcher = new EntitySaveEventDispatcher();
    $loader = makeListenerLoader(new ServiceContainer());
    $listener = new class implements EntitySaveEventListenerInterface {
        public bool $handled = false;

        public function handle(EntitySaveContext $context): void
        {
            $this->handled = true;
        }
    };

    // Act
    $loader->attachFromConfig($dispatcher, [EntitySaveLifecycle::SAVE_BEFORE => [$listener]]);
    $dispatcher->dispatch(EntitySaveLifecycle::SAVE_BEFORE, makeDiscoveryContext());

    // Assert
    expect($listener->handled)->toBeTrue();
});

test('resolves a class-string listener from the container', function () {
    // Arrange - register a listener instance in the container under its class name.
    $services = new ServiceContainer();
    $listener = new class implements EntitySaveEventListenerInterface {
        public function handle(EntitySaveContext $context): void
        {
        }
    };
    $services->set($listener::class, $listener);

    $dispatcher = new EntitySaveEventDispatcher();
    $loader = makeListenerLoader($services);

    // Act
    $loader->attachFromConfig($dispatcher, [EntitySaveLifecycle::VALIDATE_AFTER => [$listener::class]]);

    // Assert
    expect($dispatcher->listeners(EntitySaveLifecycle::VALIDATE_AFTER))->toHaveCount(1);
});

test('throws a descriptive error for an invalid listener entry', function () {
    $loader = makeListenerLoader(new ServiceContainer());
    $dispatcher = new EntitySaveEventDispatcher();

    expect(fn () => $loader->attachFromConfig($dispatcher, [
        EntitySaveLifecycle::VALIDATE_AFTER => [123],
    ]))->toThrow(ZoosperException::class);
});

test('throws when the listeners value is not a list', function () {
    $loader = makeListenerLoader(new ServiceContainer());
    $dispatcher = new EntitySaveEventDispatcher();

    expect(fn () => $loader->attachFromConfig($dispatcher, [
        EntitySaveLifecycle::VALIDATE_AFTER => 'not-an-array',
    ]))->toThrow(ZoosperException::class);
});
