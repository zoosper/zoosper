<?php

declare(strict_types=1);

use Zoosper\Core\Http\Request;
use Zoosper\Core\Http\Response;
use Zoosper\Core\Module\Module;
use Zoosper\Core\Module\ModuleRegistry;
use Zoosper\Core\Routing\ModuleRouteLoader;
use Zoosper\Core\Routing\Router;

final class DiscoveryBoundaryController
{
    public function __invoke(Request $request): Response
    {
        return Response::html('ok');
    }
}

it('preserves route discovery flag truthiness and filesystem fallback', function (string $kind, array $discovery, bool $exists, bool $expected): void {
    $root = sys_get_temp_dir() . '/zoosper-route-discovery-' . bin2hex(random_bytes(6));
    mkdir($root . '/config', 0775, true);
    $file = $root . '/config/' . $kind . '_routes.php';
    if ($exists) {
        file_put_contents($file, '<?php return ' . var_export([[
            'path' => '/example', 'controller' => DiscoveryBoundaryController::class,
            'public' => true, 'auth' => 'anonymous',
        ]], true) . ';');
    }

    try {
        $registry = new ModuleRegistry($root);
        (new ReflectionProperty($registry, 'cachedModules'))->setValue($registry, [new Module('example', $root, discovery: $discovery)]);
        $router = new Router();
        $loader = new ModuleRouteLoader($registry);
        if ($kind === 'admin') {
            $loader->registerAdminRoutes($router);
        } else {
            $loader->registerApiRoutes($router);
        }
        expect($router->allowedMethods('/example') !== [])->toBe($expected);
    } finally {
        if ($exists) {
            unlink($file);
        }
        rmdir($root . '/config');
        rmdir($root);
    }
})->with(['admin', 'api'])->with(function (): array {
    return [
        'missing present' => [[], true, true],
        'missing absent' => [[], false, false],
        'null' => [['routes_admin' => null, 'routes_api' => null], true, true],
        'false' => [['routes_admin' => false, 'routes_api' => false], true, false],
        'true' => [['routes_admin' => true, 'routes_api' => true], true, true],
        'integer zero' => [['routes_admin' => 0, 'routes_api' => 0], true, false],
        'integer one' => [['routes_admin' => 1, 'routes_api' => 1], true, true],
        'string zero' => [['routes_admin' => '0', 'routes_api' => '0'], true, false],
        'string one' => [['routes_admin' => '1', 'routes_api' => '1'], true, true],
    ];
});