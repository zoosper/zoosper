<?php

declare(strict_types=1);

namespace Zoosper\Admin\Form;

use InvalidArgumentException;

/**
 * Builds an admin form provider registry from configuration.
 *
 * This is the bridge between module-level configuration and rendered admin
 * forms. Core modules provide default section providers now; third-party modules
 * can later contribute additional provider classes through merged config or
 * service-provider registration without touching controller code.
 */
final readonly class AdminFormConfigProviderFactory
{
    /**
     * @param array<string, mixed> $config Raw `admin_forms` configuration.
     * @param array<string, list<class-string<AdminFormSectionProviderInterface>>> $fallbackProviders
     */
    public function create(array $config, array $fallbackProviders = []): AdminFormProviderRegistry
    {
        $providersByHandle = $this->normalise($config);
        if ($providersByHandle === []) {
            $providersByHandle = $fallbackProviders;
        }

        $registry = new AdminFormProviderRegistry();
        foreach ($providersByHandle as $formHandle => $providerClasses) {
            foreach ($providerClasses as $providerClass) {
                $provider = $this->instantiateProvider($providerClass);
                if ($provider->formHandle() !== $formHandle) {
                    throw new InvalidArgumentException(
                        'Admin form provider ' . $providerClass . ' declares handle ' . $provider->formHandle() . ' but was registered for ' . $formHandle . '.',
                    );
                }

                $registry->add($provider);
            }
        }

        return $registry;
    }

    /**
     * @param array<string, mixed> $config
     *
     * @return array<string, list<class-string<AdminFormSectionProviderInterface>>>
     */
    private function normalise(array $config): array
    {
        $forms = isset($config['forms']) && is_array($config['forms']) ? $config['forms'] : $config;
        $normalised = [];

        foreach ($forms as $formHandle => $providerClasses) {
            if (!is_string($formHandle) || !is_array($providerClasses)) {
                continue;
            }

            foreach ($providerClasses as $providerClass) {
                if (!is_string($providerClass) || $providerClass === '') {
                    continue;
                }

                $normalised[$formHandle][] = $providerClass;
            }
        }

        return $normalised;
    }

    /** @param class-string<AdminFormSectionProviderInterface> $providerClass */
    private function instantiateProvider(string $providerClass): AdminFormSectionProviderInterface
    {
        if (!class_exists($providerClass)) {
            throw new InvalidArgumentException('Admin form provider class does not exist: ' . $providerClass);
        }

        $provider = new $providerClass();
        if (!$provider instanceof AdminFormSectionProviderInterface) {
            throw new InvalidArgumentException('Admin form provider must implement AdminFormSectionProviderInterface: ' . $providerClass);
        }

        return $provider;
    }
}
