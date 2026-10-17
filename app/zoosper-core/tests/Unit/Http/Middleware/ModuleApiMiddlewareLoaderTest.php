<?php

declare(strict_types=1);

namespace Zoosper\Core\Tests\Unit\Http\Middleware;

use Zoosper\Core\Container\ServiceContainer;
use Zoosper\Core\Http\Middleware\ModuleApiMiddlewareLoader;
use Zoosper\Core\Http\Middleware\RouteContext;
use Zoosper\Core\Http\Middleware\RouteMiddleware;
use Zoosper\Core\Http\Request;
use Zoosper\Core\Http\Response;
use Zoosper\Core\Module\ModuleRegistry;

final class ApiLoaderFixtureMiddleware implements RouteMiddleware
{
    #[\Override]
    public function process(Request $request, RouteContext $context, callable $next): Response
    {
        return $next($request);
    }
}

it('discovers and resolves module-owned API middleware', function (): void {
    $root = sys_get_temp_dir() . '/zoosper-api-middleware-' . bin2hex(random_bytes(4));
    mkdir($root . '/app/acme-test/config', 0775, true);
    file_put_contents($root . '/app/acme-test/module.php', "<?php\n\ndeclare(strict_types=1);\n\nreturn ['api_middleware' => true];\n");
    file_put_contents($root . '/app/acme-test/config/api_middleware.php', '<?php return [' . var_export(ApiLoaderFixtureMiddleware::class, true) . '];');
    $services = new ServiceContainer();
    $fixture = new ApiLoaderFixtureMiddleware();
    $services->set(ApiLoaderFixtureMiddleware::class, $fixture);
    expect((new ModuleApiMiddlewareLoader(new ModuleRegistry($root), $services))->load())->toBe([$fixture]);
});
