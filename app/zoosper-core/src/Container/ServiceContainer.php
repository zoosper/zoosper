<?php

declare(strict_types=1);

namespace Zoosper\Core\Container;

use RuntimeException;

final class ServiceContainer
{
    /** @var array<string, object> */
    private array $services = [];

    public function set(string $id, object $service): void
    {
        $this->services[$id] = $service;
    }

    /** @template T of object @param class-string<T>|string $id @return T|object */
    public function get(string $id): object
    {
        if (!$this->has($id)) {
            throw new RuntimeException('Service is not registered: ' . $id);
        }
        return $this->services[$id];
    }

    public function has(string $id): bool
    {
        return isset($this->services[$id]);
    }

    /** @return array<string, object> */
    public function all(): array
    {
        return $this->services;
    }
}
