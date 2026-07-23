<?php

declare(strict_types=1);

namespace Zoosper\Core\Plugin;

/**
 * Creates method interceptor instances from plugin definitions.
 *
 * The resolver seam keeps the plugin foundation independent from a concrete DI
 * container while allowing a container-backed resolver to be introduced later.
 */
final readonly class MethodPluginFactory
{
    public function __construct(
        private MethodPluginResolverInterface $resolver = new ReflectionMethodPluginResolver(),
    ) {
    }

    public function create(MethodPluginDefinition $definition): MethodInterceptorInterface
    {
        $plugin = $this->resolver->resolve($definition->pluginClass);

        if (!$plugin instanceof MethodInterceptorInterface) {
            throw MethodPluginException::pluginInterfaceMismatch($definition->pluginClass);
        }

        return $plugin;
    }
}
