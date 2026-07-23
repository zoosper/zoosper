<?php

declare(strict_types=1);

namespace Zoosper\Core\Plugin;

use RuntimeException;

/**
 * Creates method interceptor instances from plugin definitions.
 *
 * This factory is deliberately tiny and conservative. A later phase can replace
 * construction with container-aware resolution once plugin discovery is wired
 * into the application factory.
 */
final class MethodPluginFactory
{
    public function create(MethodPluginDefinition $definition): MethodInterceptorInterface
    {
        if (!class_exists($definition->pluginClass)) {
            throw new RuntimeException(sprintf('Method plugin class does not exist: %s', $definition->pluginClass));
        }

        $plugin = new $definition->pluginClass();

        if (!$plugin instanceof MethodInterceptorInterface) {
            throw new RuntimeException(sprintf(
                'Method plugin class must implement %s: %s',
                MethodInterceptorInterface::class,
                $definition->pluginClass
            ));
        }

        return $plugin;
    }
}
