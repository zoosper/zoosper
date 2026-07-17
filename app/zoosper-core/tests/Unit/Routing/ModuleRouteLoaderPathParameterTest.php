<?php

declare(strict_types=1);

namespace Zoosper\Core\Tests\Unit\Routing;

use Zoosper\Core\Http\Middleware\RouteContext;
use Zoosper\Core\Http\Middleware\RouteMiddleware;
use Zoosper\Core\Http\Request;
use Zoosper\Core\Http\Response;
use Zoosper\Core\Module\ModuleRegistry;
use Zoosper\Core\Routing\ModuleRouteLoader;
use Zoosper\Core\Routing\Router;

final class RouteParameterTestController
{
    public static ?string $capturedId = null;

    public function show(Request $request): Response
    {
        self::$capturedId = $request->routeParam('id');

        return Response::html('ok');
    }
}

final class RouteParameterRecordingMiddleware implements RouteMiddleware
{
    public static bool $ran = false;
    public static ?string $contextPath = null;

    public function process(Request $request, RouteContext $context, callable $next): Response
    {
        self::$ran = true;
        self::$contextPath = $context->path;

        return $next($request);
    }
}

test('module admin routes may declare constrained path parameters and still run middleware', function () {
    $root = sys_get_temp_dir() . '/zoosper-module-route-params-' . bin2hex(random_bytes(4));
    mkdir($root . '/app/acme-test/config', 0775, true);

    file_put_contents($root . '/app/acme-test/module.php', "<?php\n\ndeclare(strict_types=1);\n\nreturn ['name' => 'Acme_Test', 'enabled' => true];\n");
    file_put_contents($root . '/app/acme-test/config/admin_routes.php', "<?php\n\ndeclare(strict_types=1);\n\nreturn [[\n    'method' => 'GET',\n    'path' => '/admin/examples/{id:\\\\d+}',\n    'controller' => \\" . RouteParameterTestController::class . "::class,\n    'action' => 'show',\n    'permission' => 'example.manage',\n]];\n");

    RouteParameterTestController::$capturedId = null;
    RouteParameterRecordingMiddleware::$ran = false;
    RouteParameterRecordingMiddleware::$contextPath = null;

    $router = new Router();
    (new ModuleRouteLoader(new ModuleRegistry($root)))->registerAdminRoutes($router, [new RouteParameterRecordingMiddleware()]);

    $router->dispatch(new Request('GET', '/admin/examples/42'));

    expect(RouteParameterRecordingMiddleware::$ran)->toBeTrue();
    expect(RouteParameterRecordingMiddleware::$contextPath)->toBe('/admin/examples/{id:\\d+}');
    expect(RouteParameterTestController::$capturedId)->toBe('42');
});
