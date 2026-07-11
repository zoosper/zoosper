<?php

declare(strict_types=1);

namespace Zoosper\Core\Container;

use RuntimeException;

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

    /**
     * Register an already-created shared service instance.
     */
    public function set(string $id, object $service): void
    {
        $this->services[$id] = $service;
        unset($this->factories[$id]);
    }

    /**
     * Register a lazy shared service factory.
     *
     * The factory is called the first time get() is used and the returned object
     * is stored as the shared service instance. Providers may use this to keep
     * bootstrapping small and avoid constructing unused optional module services.
     *
     * @param callable(self): object $factory
     */
    public function factory(string $id, callable $factory): void
    {
        if (isset($this->services[$id])) {
            unset($this->services[$id]);
        }

        $this->factories[$id] = $factory;
    }

    /**
     * Register an alias so one service ID resolves to another.
     */
    public function alias(string $id, string $targetId): void
    {
        $this->factory($id, static fn (self $services): object => $services->get($targetId));
    }

    /**
     * Resolve a registered service by ID.
     *
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
            $service = ($this->factories[$id])($this);
            if (!is_object($service)) {
                throw new RuntimeException('Service factory did not return an object for: ' . $id);
            }

            $this->services[$id] = $service;

            return $service;
        }

        throw new RuntimeException('Service is not registered: ' . $id);
    }

    /**
     * Check whether a service instance or factory is registered.
     */
    public function has(string $id): bool
    {
        return isset($this->services[$id]) || isset($this->factories[$id]);
    }

    /**
     * Return already-instantiated shared services.
     *
     * @return array<string, object>
     */
    public function all(): array
    {
        return $this->services;
    }

    /**
     * Return all registered service IDs, including lazy factories.
     *
     * @return list<string>
     */
    public function ids(): array
    {
        return array_values(array_unique(array_merge(array_keys($this->services), array_keys($this->factories))));
    }
}
