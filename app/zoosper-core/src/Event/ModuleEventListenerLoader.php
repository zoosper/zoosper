<?php

declare(strict_types=1);

namespace Zoosper\Core\Event;

use Zoosper\Core\Container\ServiceContainer;
use Zoosper\Core\Exception\ZoosperException;
use Zoosper\Core\Module\ModuleRegistry;

/**
 * Discovers general event listeners contributed by modules.
 *
 * Modules declare listeners in config/events.php as:
 *
 *     return ['page.published' => [MyListener::class]];
 */
final readonly class ModuleEventListenerLoader
{
    public function __construct(private ModuleRegistry $modules, private ServiceContainer $services)
    {
    }

    public function attach(EventDispatcherInterface $dispatcher): void
    {
        foreach ($this->modules->enabledModules() as $module) {
            $file = $module->configPath('events.php');
            if (!is_file($file)) {
                continue;
            }

            $config = require $file;
            if (!is_array($config)) {
                throw new ZoosperException(
                    message: 'Event listener config must return an array: ' . $file,
                    context: 'Module `' . $module->name . '` has a config/events.php that did not return an array.',
                    suggestion: "Return an array mapping event names to listener lists, e.g. ['page.published' => [MyListener::class]].",
                    docsUrl: 'docs/contributor/writing-event-listeners.md',
                    details: ['module' => $module->name, 'file' => $file, 'returned_type' => get_debug_type($config)],
                );
            }

            $this->attachFromConfig($dispatcher, $config, $module->name, $file);
        }
    }

    /** @param array<string, mixed> $config */
    public function attachFromConfig(
        EventDispatcherInterface $dispatcher,
        array $config,
        string $moduleName = '(inline)',
        string $file = '(inline)',
    ): void {
        foreach ($config as $eventName => $listeners) {
            if (!is_string($eventName) || $eventName === '') {
                throw new ZoosperException(
                    message: 'Event listener config has an invalid event key.',
                    context: 'Event config keys must be non-empty event-name strings.',
                    suggestion: "Use a dot-namespaced event name such as 'page.published'.",
                    docsUrl: 'docs/contributor/writing-event-listeners.md',
                    details: ['module' => $moduleName, 'file' => $file, 'event_key_type' => get_debug_type($eventName)],
                );
            }

            if (!is_iterable($listeners)) {
                throw new ZoosperException(
                    message: 'Event listeners for event must be a list: ' . $eventName,
                    context: 'Each event maps to a list of listeners, but a non-iterable value was given.',
                    suggestion: "Wrap the listener in an array, e.g. 'page.published' => [MyListener::class].",
                    docsUrl: 'docs/contributor/writing-event-listeners.md',
                    details: ['module' => $moduleName, 'file' => $file, 'event' => $eventName, 'value_type' => get_debug_type($listeners)],
                );
            }

            foreach ($listeners as $listener) {
                $dispatcher->listen($eventName, $this->resolveListener($listener, $eventName, $moduleName, $file));
            }
        }
    }

    private function resolveListener(mixed $listener, string $eventName, string $moduleName, string $file): callable|EventListenerInterface
    {
        if ($listener instanceof EventListenerInterface) {
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
                    message: 'Event listener class not found: ' . $listener,
                    context: 'A listener was declared by class-string but the class could not be located.',
                    suggestion: 'Ensure the class exists and is autoloadable, or register it in the module config/services.php and reference it by class-string.',
                    docsUrl: 'docs/contributor/writing-event-listeners.md',
                    details: ['module' => $moduleName, 'file' => $file, 'event' => $eventName, 'listener' => $listener],
                );
            }

            if (!($resolved instanceof EventListenerInterface) && !is_callable($resolved)) {
                throw new ZoosperException(
                    message: 'Event listener must implement EventListenerInterface or be callable: ' . $listener,
                    context: 'The resolved listener is neither an EventListenerInterface instance nor callable.',
                    suggestion: 'Implement EventListenerInterface::handle(object $event): void on the listener class.',
                    docsUrl: 'docs/contributor/writing-event-listeners.md',
                    details: ['module' => $moduleName, 'file' => $file, 'event' => $eventName, 'listener' => $listener, 'resolved_type' => get_debug_type($resolved)],
                );
            }

            return $resolved;
        }

        throw new ZoosperException(
            message: 'Invalid event listener entry.',
            context: 'A listener entry must be an EventListenerInterface instance, a callable, or a class-string.',
            suggestion: 'Use [MyListener::class], a closure, or a listener instance.',
            docsUrl: 'docs/contributor/writing-event-listeners.md',
            details: ['module' => $moduleName, 'file' => $file, 'event' => $eventName, 'listener_type' => get_debug_type($listener)],
        );
    }
}
