<?php

declare(strict_types=1);

namespace Zoosper\Core\Container;

use Throwable;
use Zoosper\Errors\ZoosperException;

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

    /** @var array<string, bool> */
    private array $loading = [];

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

    /** @param callable(self, object): object $decorator */
    public function decorate(string $id, callable $decorator): void
    {
        $previousService = $this->services[$id] ?? null;
        $previousFactory = $this->factories[$id] ?? null;
        if ($previousService === null && $previousFactory === null) {
            throw new \RuntimeException('Cannot decorate unregistered service: '.$id);
        }
        unset($this->services[$id]);
        $this->factories[$id] = static function (self $services) use ($decorator, $previousService, $previousFactory): object {
            $inner = $previousService ?? $previousFactory($services);
            return $decorator($services, $inner);
        };
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

        if (isset($this->loading[$id])) {
            throw new ZoosperException(
                message: 'Circular dependency detected for: ' . $id,
                context: 'The service container was already resolving this ID when a recursive request for the same ID occurred.',
                suggestion: 'Refactor your constructors to break the circularity, or use a lazy proxy/factory decorator.',
                details: ['service_id' => $id, 'loading_stack' => array_keys($this->loading)]
            );
        }

        $this->loading[$id] = true;

        try {
            if (isset($this->factories[$id])) {
                $service = ($this->factories[$id])($this);

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

            if (class_exists($id)) {
                $service = $this->autowire($id);
                $this->services[$id] = $service;

                return $service;
            }
        } catch (Throwable $exception) {
            if ($exception instanceof ZoosperException) {
                throw $exception;
            }

            throw new ZoosperException(
                message: 'Service resolution failed for: ' . $id,
                context: 'An error occurred while trying to resolve or autowire the requested service.',
                suggestion: 'Check the error message and previous exception for details. If autowiring, ensure all dependencies are registrable or autowireable.',
                details: [
                    'service_id' => $id,
                    'registered_service_ids' => $this->ids(),
                ],
                previous: $exception,
            );
        } finally {
            unset($this->loading[$id]);
        }

        throw new ZoosperException(
            message: 'Service is not registered: ' . $id,
            context: 'A service was requested from the container, but no enabled module registered an instance or factory for this ID, and it could not be autowired.',
            suggestion: 'Add a service definition to your module config/services.php, enable the module that provides this service, or check for a typo in the service ID. Then run `php tools/verify-service-providers.php`.',
            docsUrl: 'docs/operations/module-development.md',
            details: [
                'service_id' => $id,
                'registered_service_ids' => $this->ids(),
            ],
        );
    }

    public function autowire(string $class): object
    {
        $reflection = new \ReflectionClass($class);
        if (!$reflection->isInstantiable()) {
            throw new ZoosperException(
                message: 'Cannot autowire non-instantiable class: ' . $class,
                context: 'The requested class exists but is abstract, an interface, or has a private constructor.',
                suggestion: 'Ensure you are requesting a concrete class, or register a manual factory for the interface.'
            );
        }

        $constructor = $reflection->getConstructor();
        if ($constructor === null) {
            $service = new $class();
            $this->services[$class] = $service;
            return $service;
        }

        $dependencies = [];
        foreach ($constructor->getParameters() as $parameter) {
            $type = $parameter->getType();
            
            if ($type instanceof \ReflectionNamedType && !$type->isBuiltin()) {
                $dependencies[] = $this->get($type->getName());
                continue;
            }

            if ($type instanceof \ReflectionUnionType) {
                $resolved = false;
                foreach ($type->getTypes() as $unionType) {
                    if ($unionType instanceof \ReflectionNamedType && !$unionType->isBuiltin()) {
                        try {
                            $dependencies[] = $this->get($unionType->getName());
                            $resolved = true;
                            break;
                        } catch (Throwable) {
                            // Try next type in union
                        }
                    }
                }
                if ($resolved) {
                    continue;
                }
            }

            if ($parameter->isDefaultValueAvailable()) {
                $dependencies[] = $parameter->getDefaultValue();
                continue;
            }

            throw new ZoosperException(
                message: 'Cannot autowire parameter `' . $parameter->getName() . '` for: ' . $class,
                context: 'The constructor requires a parameter that could not be automatically resolved (unsupported type, builtin type, or no matching registered service).',
                suggestion: 'Manually register a factory for this class in config/services.php to provide specific arguments.'
            );
        }

        $service = $reflection->newInstanceArgs($dependencies);
        $this->services[$class] = $service;
        return $service;
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










