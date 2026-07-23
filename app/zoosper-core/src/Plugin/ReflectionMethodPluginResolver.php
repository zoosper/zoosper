<?php

declare(strict_types=1);

namespace Zoosper\Core\Plugin;

/**
 * Default plugin resolver that creates no-argument plugin classes directly.
 */
final class ReflectionMethodPluginResolver implements MethodPluginResolverInterface
{
    public function resolve(string $pluginClass): object
    {
        if (!class_exists($pluginClass)) {
            throw MethodPluginException::pluginClassMissing($pluginClass);
        }

        return new $pluginClass();
    }
}
