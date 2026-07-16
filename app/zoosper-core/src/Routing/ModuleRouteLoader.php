<?php

declare(strict_types=1);

namespace Zoosper\Core\Routing;

use ReflectionClass;
use Zoosper\Core\Exception\ZoosperException;
use Zoosper\Core\Http\Middleware\MiddlewarePipeline;
use Zoosper\Core\Http\Middleware\RouteContext;
use Zoosper\Core\Http\Middleware\RouteMiddleware;
use Zoosper\Core\Http\Request;
use Zoosper\Core\Http\Response;
use Zoosper\Core\Module\ModuleRegistry;

final readonly class ModuleRouteLoader
{
    /** @param array<class-string, object> $controllers */
    public function __construct(
        private ModuleRegistry $modules,
        private array $controllers = [],
    ) {
    }

    /**
     * Phase 1.33: admin routes are wrapped in the given middleware pipeline
     * (auth guard, etc). API routes are intentionally NOT wrapped so the
     * stateless API is unaffected.
     *
     * @param list<RouteMiddleware> $middleware
     */
    public function registerAdminRoutes(Router $router, array $middleware = []): void
    {
        $this->registerRoutesFromConfig($router, 'admin_routes.php', $middleware);
    }

    public function registerApiRoutes(Router $router): void
    {
        $this->registerRoutesFromConfig($router, 'api_routes.php', []);
    }

    /** @param list<RouteMiddleware> $middleware */
    private function registerRoutesFromConfig(Router $router, string $configFile, array $middleware): void
    {
        foreach ($this->load($configFile) as $route) {
            $router->map($route->method, $route->path, $this->handlerFor($route, $middleware));
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
                throw new ZoosperException(
                    message: 'Route config must return an array: ' . $file,
                    context: 'Module `' . $module->name . '` has a route config file that did not return an array.',
                    suggestion: 'Return a list of route arrays with method, path, controller and action keys.',
                    docsUrl: 'docs/operations/troubleshooting-helpful-errors.md',
                    details: ['module' => $module->name, 'file' => $file, 'returned_type' => get_debug_type($config)],
                );
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

    /**
     * @param list<RouteMiddleware> $middleware
     * @return callable
     */
    private function handlerFor(ModuleRouteDefinition $route, array $middleware): callable
    {
        $controller = $this->controllerFor($route->controller, $route);

        if (!method_exists($controller, $route->action)) {
            throw new ZoosperException(
                message: sprintf('Route action does not exist: %s::%s', $route->controller, $route->action),
                context: sprintf('Route: %s %s', $route->method, $route->path),
                suggestion: 'Create the action method on the controller or update the route config action value. Then run the relevant route diagnostics.',
                docsUrl: 'docs/operations/troubleshooting-helpful-errors.md',
                details: ['controller' => $route->controller, 'action' => $route->action, 'method' => $route->method, 'path' => $route->path],
            );
        }

        $action = $route->action;

        if ($middleware === []) {
            return [$controller, $action];
        }

        $context = new RouteContext($route->method, $route->path, $route->public, $route->permission);
        $pipeline = new MiddlewarePipeline($middleware);

        return static fn (Request $request): Response => $pipeline->handle(
            $request,
            $context,
            static fn (Request $req): Response => $controller->{$action}($req),
        );
    }

    private function controllerFor(string $class, ModuleRouteDefinition $route): object
    {
        if (isset($this->controllers[$class])) {
            return $this->controllers[$class];
        }

        if (!class_exists($class)) {
            throw new ZoosperException(
                message: 'Route controller class does not exist: ' . $class,
                context: sprintf('Route: %s %s', $route->method, $route->path),
                suggestion: 'Check the controller namespace, Composer autoload mapping, and module config/controllers.php registration. Then run `composer dump-autoload`.',
                docsUrl: 'docs/operations/troubleshooting-helpful-errors.md',
                details: ['controller' => $class, 'method' => $route->method, 'path' => $route->path],
            );
        }

        $reflection = new ReflectionClass($class);
        $constructor = $reflection->getConstructor();

        if ($constructor !== null && $constructor->getNumberOfRequiredParameters() > 0) {
            throw new ZoosperException(
                message: 'Route controller requires dependencies and was not provided to ModuleRouteLoader: ' . $class,
                context: sprintf('Route: %s %s. Controller has required constructor parameters but was not registered in config/controllers.php.', $route->method, $route->path),
                suggestion: 'Add the controller factory to your module config/controllers.php so dependencies can be injected from ServiceContainer.',
                docsUrl: 'docs/operations/troubleshooting-helpful-errors.md',
                details: ['controller' => $class, 'method' => $route->method, 'path' => $route->path],
            );
        }

        return $reflection->newInstance();
    }

    private function assertValid(ModuleRouteDefinition $route): void
    {
        if ($route->path === '' || !str_starts_with($route->path, '/')) {
            throw new ZoosperException(
                message: 'Route path must start with / for controller: ' . $route->controller,
                context: 'Invalid route definition found while loading module routes.',
                suggestion: 'Update the route path to start with `/`, for example `/admin/example` or `/api/example`.',
                docsUrl: 'docs/operations/troubleshooting-helpful-errors.md',
                details: ['controller' => $route->controller, 'path' => $route->path],
            );
        }
        if ($route->controller === '') {
            throw new ZoosperException('Route controller is required for path: ' . $route->path, 'Invalid route definition found while loading module routes.', 'Add a controller class to the route config.', 'docs/operations/troubleshooting-helpful-errors.md', ['path' => $route->path]);
        }
        if ($route->action === '') {
            throw new ZoosperException('Route action is required for path: ' . $route->path, 'Invalid route definition found while loading module routes.', 'Add an action method name to the route config.', 'docs/operations/troubleshooting-helpful-errors.md', ['path' => $route->path, 'controller' => $route->controller]);
        }
        if (!in_array($route->method, ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            throw new ZoosperException('Unsupported route method: ' . $route->method, 'Invalid route definition found while loading module routes.', 'Use one of GET, POST, PUT, PATCH or DELETE.', 'docs/operations/troubleshooting-helpful-errors.md', ['method' => $route->method, 'path' => $route->path]);
        }
    }
}