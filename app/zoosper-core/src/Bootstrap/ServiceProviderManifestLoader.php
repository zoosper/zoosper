<?php

declare(strict_types=1);

namespace Zoosper\Core\Bootstrap;

use ReflectionClass;
use RuntimeException;

/**
 * Loads and registers service providers from a manifest file.
 *
 * The loader is intentionally conservative. It supports the current Zoosper
 * provider manifest shape while keeping construction flexible for providers
 * that accept `$basePath` and optional configuration arrays.
 */
final readonly class ServiceProviderManifestLoader
{
    public function __construct(private string $basePath)
    {
    }

    public function load(object $container, ?string $manifestPath = null): int
    {
        $manifestPath ??= $this->basePath . '/config/service_providers.php';
        if (!is_file($manifestPath)) {
            return 0;
        }

        $manifest = require $manifestPath;
        if (!is_array($manifest)) {
            throw new RuntimeException('Service provider manifest must return an array.');
        }

        $count = 0;
        foreach ($this->providersFromManifest($manifest) as $providerClass) {
            if (!class_exists($providerClass)) {
                throw new RuntimeException('Configured service provider does not exist: ' . $providerClass);
            }

            $provider = $this->instantiateProvider($providerClass);
            if (!method_exists($provider, 'register')) {
                throw new RuntimeException('Service provider must expose register(object $container): ' . $providerClass);
            }

            $provider->register($container);
            $count++;
        }

        return $count;
    }

    /** @param array<mixed> $manifest */
    private function providersFromManifest(array $manifest): array
    {
        $providers = isset($manifest['providers']) && is_array($manifest['providers'])
            ? $manifest['providers']
            : $manifest;

        return array_values(array_unique(array_filter($providers, 'is_string')));
    }

    private function instantiateProvider(string $providerClass): object
    {
        $reflection = new ReflectionClass($providerClass);
        $constructor = $reflection->getConstructor();
        if ($constructor === null || $constructor->getNumberOfParameters() === 0) {
            return $reflection->newInstance();
        }

        $arguments = [];
        foreach ($constructor->getParameters() as $parameter) {
            $name = $parameter->getName();
            if ($name === 'basePath') {
                $arguments[] = $this->basePath;

                continue;
            }

            if ($name === 'i18nConfig') {
                $arguments[] = $this->config('i18n');

                continue;
            }

            if ($parameter->isDefaultValueAvailable()) {
                $arguments[] = $parameter->getDefaultValue();

                continue;
            }

            throw new RuntimeException('Unable to resolve constructor parameter $' . $name . ' for service provider: ' . $providerClass);
        }

        return $reflection->newInstanceArgs($arguments);
    }

    /** @return array<string, mixed> */
    private function config(string $name): array
    {
        $path = $this->basePath . '/config/' . $name . '.php';
        if (!is_file($path)) {
            return [];
        }

        $config = require $path;

        return is_array($config) ? $config : [];
    }
}
