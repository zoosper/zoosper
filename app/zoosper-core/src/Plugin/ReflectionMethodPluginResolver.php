<?php

declare(strict_types=1);

namespace Zoosper\Core\Plugin;

use RuntimeException;

/**
 * Default plugin resolver that creates no-argument plugin classes directly.
 */
final class ReflectionMethodPluginResolver implements MethodPluginResolverInterface
{
    public function resolve(string $pluginClass): object
    {
        if (!class_exists($pluginClass)) {
            throw new RuntimeException(sprintf('Method plugin class does not exist: %s', $pluginClass));
        }

        return new $pluginClass();
    }
}
