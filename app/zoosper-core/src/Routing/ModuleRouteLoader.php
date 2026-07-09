<?php

declare(strict_types=1);

namespace Zoosper\Core\Routing;

use ReflectionClass;
use RuntimeException;
use Zoosper\Core\Module\ModuleRegistry;

final readonly class ModuleRouteLoader
{
    /** @param array<class-string, object> $controllers */
    public function __construct(
        private ModuleRegistry $modules,
        private array $controllers = [],
    ) {
    }

    public function registerAdminRoutes(Router $router): void
    {
        $this->registerRoutesFromConfig($router, 'admin_routes.php');
    }

    public function registerApiRoutes(Router $router): void
    {
        $this->registerRoutesFromConfig($router, 'api_routes.php');
    }

    private function registerRoutesFromConfig(Router $router, string $configFile): void
    {
        foreach ($this->load($configFile) as $route) {
            $router->map($route->method, $route->path, $this->handlerFor($route));
        }
    }

    /** @return list<ModuleRouteDefinition> */
    private function load(string $configFile): array
    {
        $routes = [];

        foreach ($this->modules->enabledModules() as $module) {
            $file = $module->configPath($configFile);
            if (!is_file($file)) {
                continue;
            }

            $config = require $file;
            if (!is_array($config)) {
                throw new RuntimeException('Route config must return an array: ' . $file);
            }

            foreach ($config as $route) {
                if (!is_array($route)) {
                    continue;
                }
                $routes[] = new ModuleRouteDefinition(
                    method: strtoupper((string) ($route['method'] ?? 'GET')),
                    path: (string) ($route['path'] ?? ''),
                    controller: (string) ($route['controller'] ?? ''),
                    action: (string) ($route['action'] ?? '__invoke'),
                    permission: isset($route['permission']) ? (string) $route['permission'] : null,
                    public: (bool) ($route['public'] ?? false),
                );
            }
        }

        foreach ($routes as $route) {
            $this->assertValid($route);
        }

        return $routes;
    }

    /** @return callable */
    private function handlerFor(ModuleRouteDefinition $route): callable
    {
        $controller = $this->controllerFor($route->controller);

        if (!method_exists($controller, $route->action)) {
            throw new RuntimeException(sprintf(
                'Route action does not exist: %s::%s for %s %s',
                $route->controller,
                $route->action,
                $route->method,
                $route->path,
            ));
        }

        return [$controller, $route->action];
    }

    private function controllerFor(string $class): object
    {
        if (isset($this->controllers[$class])) {
            return $this->controllers[$class];
        }

        if (!class_exists($class)) {
            throw new RuntimeException('Route controller class does not exist: ' . $class);
        }

        $reflection = new ReflectionClass($class);
        $constructor = $reflection->getConstructor();

        if ($constructor !== null && $constructor->getNumberOfRequiredParameters() > 0) {
            throw new RuntimeException('Route controller requires dependencies and was not provided to ModuleRouteLoader: ' . $class);
        }

        return $reflection->newInstance();
    }

    private function assertValid(ModuleRouteDefinition $route): void
    {
        if ($route->path === '' || !str_starts_with($route->path, '/')) {
            throw new RuntimeException('Route path must start with / for controller: ' . $route->controller);
        }
        if ($route->controller === '') {
            throw new RuntimeException('Route controller is required for path: ' . $route->path);
        }
        if ($route->action === '') {
            throw new RuntimeException('Route action is required for path: ' . $route->path);
        }
        if (!in_array($route->method, ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            throw new RuntimeException('Unsupported route method: ' . $route->method);
        }
    }
}
