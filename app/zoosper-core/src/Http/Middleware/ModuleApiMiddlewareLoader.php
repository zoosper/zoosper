<?php

declare(strict_types=1);

namespace Zoosper\Core\Http\Middleware;

use Zoosper\Core\Container\ServiceContainer;
use Zoosper\Core\Module\ModuleRegistry;
use Zoosper\Errors\ZoosperException;

final readonly class ModuleApiMiddlewareLoader
{
    public function __construct(private ModuleRegistry $modules, private ServiceContainer $services) {}

    /** @return list<RouteMiddleware> */
    public function load(): array
    {
        $result = [];
        foreach ($this->modules->enabledModules() as $module) {
            $file = $module->configPath('api_middleware.php');
            $declared = ($module->discovery['api_middleware'] ?? null) === true;
            if (!$declared && !is_file($file)) {
                continue;
            }
            /** @psalm-suppress UnresolvableInclude Module-owned middleware paths are resolved at runtime. */
            $entries = require $file;
            if (!is_array($entries)) {
                throw new ZoosperException('API middleware config must return an array: ' . $file);
            }
            foreach ($entries as $entry) {
                if (!is_string($entry) || !$this->services->has($entry)) {
                    throw new ZoosperException('API middleware is not registered: ' . get_debug_type($entry));
                }
                $middleware = $this->services->get($entry);
                if (!$middleware instanceof RouteMiddleware) {
                    throw new ZoosperException('API middleware must implement RouteMiddleware: ' . $entry);
                }
                $result[] = $middleware;
            }
        }
        return $result;
    }
}
