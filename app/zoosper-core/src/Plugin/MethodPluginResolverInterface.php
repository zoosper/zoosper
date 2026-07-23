<?php

declare(strict_types=1);

namespace Zoosper\Core\Plugin;

/**
 * Resolves a plugin class into a runtime interceptor object.
 *
 * A later phase can implement this interface using the application container.
 */
interface MethodPluginResolverInterface
{
    public function resolve(string $pluginClass): object;
}
