<?php

declare(strict_types=1);

namespace Zoosper\Core\Tests\Unit\Http\Middleware;

use Zoosper\Core\Container\ServiceContainer;
use Zoosper\Core\Exception\ZoosperException;
use Zoosper\Core\Http\Middleware\ModuleAdminMiddlewareLoader;
use Zoosper\Core\Http\Middleware\RouteContext;
use Zoosper\Core\Http\Middleware\RouteMiddleware;
use Zoosper\Core\Http\Request;
use Zoosper\Core\Http\Response;

function makeMiddlewareLoader(ServiceContainer $services): ModuleAdminMiddlewareLoader
{
    $ref = new \ReflectionClass(ModuleAdminMiddlewareLoader::class);
    $loader = $ref->newInstanceWithoutConstructor();
    $ref->getProperty('services')->setValue($loader, $services);

    return $loader;
}

test('resolves a class-string middleware from the container', function () {
    $services = new ServiceContainer();
    $middleware = new class implements RouteMiddleware {
        public function process(Request $request, RouteContext $context, callable $next): Response
        {
            return $next($request);
        }
    };
    $services->set($middleware::class, $middleware);

    $resolved = makeMiddlewareLoader($services)->resolveEntries([$middleware::class]);

    expect($resolved)->toHaveCount(1);
    expect($resolved[0])->toBeInstanceOf(RouteMiddleware::class);
});

test('throws when a middleware entry is invalid', function () {
    $loader = makeMiddlewareLoader(new ServiceContainer());

    expect(fn () => $loader->resolveEntries([123]))->toThrow(ZoosperException::class);
});