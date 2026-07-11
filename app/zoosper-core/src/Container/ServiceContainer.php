<?php

declare(strict_types=1);

namespace Zoosper\Core\Container;

use Throwable;
use Zoosper\Core\Exception\ZoosperException;

/**
 * Lightweight application service container with lazy factory support.
 *
 * Module-owned `config/services.php` files should register services here using
 * factories. Business services should still receive dependencies through
 * constructors and should not use the container directly. This keeps Zoosper
 * modular without turning application code into a service-locator pattern.
 */
final class ServiceContainer
{
    /** @var array<string, object> */
    private array $services = [];

    /** @var array<string, callable(self): object> */
    private array $factories = [];

    public function set(string $id, object $service): void
    {
        $this->services[$id] = $service;
        unset($this->factories[$id]);
    }

    /** @param callable(self): object $factory */
    public function factory(string $id, callable $factory): void
    {
        if (isset($this->services[$id])) {
            unset($this->services[$id]);
        }

        $this->factories[$id] = $factory;
    }

    public function alias(string $id, string $targetId): void
    {
        $this->factory($id, static fn (self $services): object => $services->get($targetId));
    }

    /**
     * @template T of object
     * @param class-string<T>|string $id
     * @return T|object
     */
    public function get(string $id): object
    {
        if (isset($this->services[$id])) {
            return $this->services[$id];
        }

        if (isset($this->factories[$id])) {
            try {
                $service = ($this->factories[$id])($this);
            } catch (Throwable $exception) {
                throw new ZoosperException(
                    message: 'Service factory failed while creating: ' . $id,
                    context: 'A lazy service factory was registered, but it threw an exception during construction.',
                    suggestion: 'Check the module config/services.php file that registers this service. Then run `php tools/verify-service-providers.php` for the detailed failing service.',
                    docsUrl: 'docs/operations/troubleshooting-helpful-errors.md',
                    details: [
                        'service_id' => $id,
                        'registered_service_ids' => $this->ids(),
                    ],
                    previous: $exception,
                );
            }

            if (!is_object($service)) {
                throw new ZoosperException(
                    message: 'Service factory did not return an object for: ' . $id,
                    context: 'Factories in config/services.php must return an object instance. Scalars, arrays and null are invalid service definitions.',
                    suggestion: 'Update the factory to return a class instance, for example: `SomeService::class => static fn (ServiceContainer $services): SomeService => new SomeService()`.',
                    docsUrl: 'docs/operations/module-development.md',
                    details: ['service_id' => $id],
                );
            }

            $this->services[$id] = $service;

            return $service;
        }

        throw new ZoosperException(
            message: 'Service is not registered: ' . $id,
            context: 'A service was requested from the container, but no enabled module registered an instance or factory for this ID.',
            suggestion: 'Add a service definition to your module config/services.php, enable the module that provides this service, or check for a typo in the service ID. Then run `php tools/verify-service-providers.php`.',
            docsUrl: 'docs/operations/module-development.md',
            details: [
                'service_id' => $id,
                'registered_service_ids' => $this->ids(),
            ],
        );
    }

    public function has(string $id): bool
    {
        return isset($this->services[$id]) || isset($this->factories[$id]);
    }

    /** @return array<string, object> */
    public function all(): array
    {
        return $this->services;
    }

    /** @return list<string> */
    public function ids(): array
    {
        return array_values(array_unique(array_merge(array_keys($this->services), array_keys($this->factories))));
    }
}
