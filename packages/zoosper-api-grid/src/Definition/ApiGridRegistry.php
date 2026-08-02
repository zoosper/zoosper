<?php

declare(strict_types=1);

namespace Zoosper\ApiGrid\Definition;

use InvalidArgumentException;

final class ApiGridRegistry
{
    /** @var array<string, ApiGridDefinition> */
    private array $definitions = [];

    /** @param iterable<ApiGridDefinition> $definitions */
    public function __construct(iterable $definitions = [])
    {
        foreach ($definitions as $definition) {
            $this->register($definition);
        }
    }

    public function register(ApiGridDefinition $definition): void
    {
        if (isset($this->definitions[$definition->key])) {
            throw new InvalidArgumentException("Duplicate API Grid key: {$definition->key}");
        }
        foreach ($this->definitions as $registered) {
            if ($registered->route === $definition->route) {
                throw new InvalidArgumentException("Duplicate API Grid route: {$definition->route}");
            }
        }
        $this->definitions[$definition->key] = $definition;
    }

    public function get(string $key): ApiGridDefinition
    {
        return $this->definitions[$key]
            ?? throw new InvalidArgumentException("Unknown API Grid: {$key}");
    }

    /** @return list<ApiGridDefinition> */
    public function all(): array
    {
        return array_values($this->definitions);
    }
}
