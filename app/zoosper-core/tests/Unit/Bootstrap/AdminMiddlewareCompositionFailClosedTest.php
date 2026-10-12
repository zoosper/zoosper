<?php

declare(strict_types=1);

it('fails closed before registering Admin routes unless the mandatory authentication middleware is discovered', function (): void {
    $source = (string) file_get_contents(
        dirname(__DIR__, 5) . '/app/zoosper-core/src/Bootstrap/ApplicationFactory.php',
    );

    $load = '$adminMiddleware = (new ModuleAdminMiddlewareLoader($modules, $services))->load();';
    $guard = 'if (!$hasAdminAuthentication) {';
    $register = '$routeLoader->registerAdminRoutes($router, $adminMiddleware, $basePath);';

    expect($source)
        ->toContain($load)
        ->toContain('instanceof AdminAuthenticationMiddleware')
        ->toContain($guard)
        ->toContain('Admin authentication middleware is missing.')
        ->and(strpos($source, $load))->toBeLessThan(strpos($source, $guard))
        ->and(strpos($source, $guard))->toBeLessThan(strpos($source, $register));
});
