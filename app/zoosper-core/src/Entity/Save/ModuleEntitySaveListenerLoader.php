<?php

declare(strict_types=1);

namespace Zoosper\Core\Entity\Save;

use Zoosper\Core\Container\ServiceContainer;
use Zoosper\Core\Exception\ZoosperException;
use Zoosper\Core\Module\ModuleRegistry;

/**
 * Discovers and attaches entity-save lifecycle listeners contributed by modules.
 *
 * Each module may declare listeners in its own `config/entity_save_listeners.php`,
 * which returns an array mapping EntitySaveLifecycle event constants to a list of
 * listener class-strings or callables. This lets modules hook the save lifecycle
 * WITHOUT editing core services.php - the "extend without touching core" rule.
 *
 * Resolution order for a listener entry:
 *   1. An EntitySaveEventListenerInterface instance  -> used as-is.
 *   2. A callable                                    -> used as-is.
 *   3. A class-string                                -> resolved from the service
 *      container if registered, otherwise constructed with `new`.
 *
 * PCI-aware: listeners handle CMS entity fields only; never place secrets/tokens
 * into listener config or logs.
 */
final readonly class ModuleEntitySaveListenerLoader
{
    public function __construct(private ModuleRegistry $modules, private ServiceContainer $services)
    {
    }

    /**
     * Discover every enabled module's listener config and attach it.
     */
    public function attach(EntitySaveEventDispatcherInterface $dispatcher): void
    {
        foreach ($this->modules->enabledModules() as $module) {
            $file = $module->configPath('entity_save_listeners.php');
            if (!is_file($file)) {
                continue;
            }

            $config = require $file;
            if (!is_array($config)) {
                throw new ZoosperException(
                    message: 'Entity save listener config must return an array: ' . $file,
                    context: 'Module `' . $module->name . '` has a config/entity_save_listeners.php that did not return an array.',
                    suggestion: 'Return an array mapping EntitySaveLifecycle event constants to a list of listener class-strings or callables.',
                    docsUrl: 'docs/contributor/writing-save-listeners.md',
                    details: ['module' => $module->name, 'file' => $file, 'returned_type' => get_debug_type($config)],
                );
            }

            $this->attachFromConfig($dispatcher, $config, $module->name, $file);
        }
    }

    /**
     * Attach listeners from an already-loaded config array.
     *
     * This is the pure, testable core of the loader. It does not touch the module
     * registry, so it can be exercised directly in unit tests.
     *
     * @param array<string, mixed> $config
     */
    public function attachFromConfig(
        EntitySaveEventDispatcherInterface $dispatcher,
        array $config,
        string $moduleName = '(inline)',
        string $file = '(inline)',
    ): void {
        foreach ($config as $event => $listeners) {
            if (!is_string($event) || $event === '') {
                throw new ZoosperException(
                    message: 'Entity save listener config has an invalid event key.',
                    context: 'Listener config keys must be non-empty EntitySaveLifecycle event-name strings.',
                    suggestion: 'Use an EntitySaveLifecycle constant, e.g. `EntitySaveLifecycle::VALIDATE_AFTER => [MyListener::class]`.',
                    docsUrl: 'docs/contributor/writing-save-listeners.md',
                    details: ['module' => $moduleName, 'file' => $file, 'event_key_type' => get_debug_type($event)],
                );
            }

            if (!is_iterable($listeners)) {
                throw new ZoosperException(
                    message: 'Entity save listeners for event must be a list: ' . $event,
                    context: 'Each event maps to a list of listeners, but a non-iterable value was given.',
                    suggestion: 'Wrap the listener in an array, e.g. `' . $event . ' => [MyListener::class]`.',
                    docsUrl: 'docs/contributor/writing-save-listeners.md',
                    details: ['module' => $moduleName, 'file' => $file, 'event' => $event, 'value_type' => get_debug_type($listeners)],
                );
            }

            foreach ($listeners as $listener) {
                $dispatcher->listen($event, $this->resolveListener($listener, $event, $moduleName, $file));
            }
        }
    }

    /**
     * Resolve a listener entry into an instance or callable.
     */
    private function resolveListener(mixed $listener, string $event, string $moduleName, string $file): callable|EntitySaveEventListenerInterface
    {
        if ($listener instanceof EntitySaveEventListenerInterface) {
            return $listener;
        }

        if (is_callable($listener)) {
            return $listener;
        }

        if (is_string($listener) && $listener !== '') {
            if ($this->services->has($listener)) {
                $resolved = $this->services->get($listener);
            } elseif (class_exists($listener)) {
                $resolved = new $listener();
            } else {
                throw new ZoosperException(
                    message: 'Entity save listener class not found: ' . $listener,
                    context: 'A listener was declared by class-string but the class could not be located.',
                    suggestion: 'Ensure the class exists and is autoloadable, or register it in the module config/services.php and reference it by class-string.',
                    docsUrl: 'docs/contributor/writing-save-listeners.md',
                    details: ['module' => $moduleName, 'file' => $file, 'event' => $event, 'listener' => $listener],
                );
            }

            if (!($resolved instanceof EntitySaveEventListenerInterface) && !is_callable($resolved)) {
                throw new ZoosperException(
                    message: 'Entity save listener must implement EntitySaveEventListenerInterface or be callable: ' . $listener,
                    context: 'The resolved listener is neither an EntitySaveEventListenerInterface instance nor callable.',
                    suggestion: 'Implement EntitySaveEventListenerInterface::handle(EntitySaveContext): void on the listener class.',
                    docsUrl: 'docs/contributor/writing-save-listeners.md',
                    details: ['module' => $moduleName, 'file' => $file, 'event' => $event, 'listener' => $listener, 'resolved_type' => get_debug_type($resolved)],
                );
            }

            return $resolved;
        }

        throw new ZoosperException(
            message: 'Invalid entity save listener entry.',
            context: 'A listener entry must be an EntitySaveEventListenerInterface instance, a callable, or a class-string.',
            suggestion: 'Use `[MyListener::class]`, a closure, or a listener instance.',
            docsUrl: 'docs/contributor/writing-save-listeners.md',
            details: ['module' => $moduleName, 'file' => $file, 'event' => $event, 'listener_type' => get_debug_type($listener)],
        );
    }
}
