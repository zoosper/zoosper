<?php

declare(strict_types=1);

namespace Zoosper\Core\Http\Middleware;

use Zoosper\Core\Container\ServiceContainer;
use Zoosper\Core\Exception\ZoosperException;
use Zoosper\Core\Module\ModuleRegistry;

/**
 * Discovers admin route middleware contributed by modules.
 *
 * Each module may declare an ordered list of middleware class-strings in
 * config/admin_middleware.php. They are resolved from the service container
 * (container-first, then `new`) and applied to admin routes only. Order is
 * module discovery order.
 */
final readonly class ModuleAdminMiddlewareLoader
{
    public function __construct(private ModuleRegistry $modules, private ServiceContainer $services)
    {
    }

    /** @return list<RouteMiddleware> */
    public function load(): array
    {
        $middleware = [];

        foreach ($this->modules->enabledModules() as $module) {
            $file = $module->configPath('admin_middleware.php');
            if (!is_file($file)) {
                continue;
            }

            $config = require $file;
            if (!is_array($config)) {
                throw new ZoosperException(
                    message: 'Admin middleware config must return an array: ' . $file,
                    context: 'Module `' . $module->name . '` config/admin_middleware.php did not return an array.',
                    suggestion: 'Return a list of RouteMiddleware class-strings, e.g. [AuthenticationMiddleware::class].',
                    docsUrl: 'docs/roadmap/phase-1.33-middleware-plan.md',
                    details: ['module' => $module->name, 'file' => $file, 'returned_type' => get_debug_type($config)],
                );
            }

            foreach ($this->resolveEntries($config, $module->name, $file) as $item) {
                $middleware[] = $item;
            }
        }

        return $middleware;
    }

    /**
     * @param array<int|string, mixed> $entries
     * @return list<RouteMiddleware>
     */
    public function resolveEntries(array $entries, string $moduleName = '(inline)', string $file = '(inline)'): array
    {
        $resolved = [];

        foreach ($entries as $entry) {
            if ($entry instanceof RouteMiddleware) {
                $resolved[] = $entry;
                continue;
            }

            if (is_string($entry) && $entry !== '') {
                if ($this->services->has($entry)) {
                    $instance = $this->services->get($entry);
                } elseif (class_exists($entry)) {
                    $instance = new $entry();
                } else {
                    throw new ZoosperException(
                        message: 'Admin middleware class not found: ' . $entry,
                        context: 'A middleware was declared by class-string but could not be located.',
                        suggestion: 'Ensure the class exists and is autoloadable, or register it in the module config/services.php.',
                        docsUrl: 'docs/roadmap/phase-1.33-middleware-plan.md',
                        details: ['module' => $moduleName, 'file' => $file, 'middleware' => $entry],
                    );
                }

                if (!$instance instanceof RouteMiddleware) {
                    throw new ZoosperException(
                        message: 'Admin middleware must implement RouteMiddleware: ' . $entry,
                        context: 'The resolved middleware does not implement the RouteMiddleware interface.',
                        suggestion: 'Implement RouteMiddleware::process() on the middleware class.',
                        docsUrl: 'docs/roadmap/phase-1.33-middleware-plan.md',
                        details: ['module' => $moduleName, 'file' => $file, 'middleware' => $entry, 'resolved_type' => get_debug_type($instance)],
                    );
                }

                $resolved[] = $instance;
                continue;
            }

            throw new ZoosperException(
                message: 'Invalid admin middleware entry.',
                context: 'A middleware entry must be a RouteMiddleware instance or a class-string.',
                suggestion: 'Use [MyMiddleware::class] or a RouteMiddleware instance.',
                docsUrl: 'docs/roadmap/phase-1.33-middleware-plan.md',
                details: ['module' => $moduleName, 'file' => $file, 'entry_type' => get_debug_type($entry)],
            );
        }

        return $resolved;
    }
}