<?php

declare(strict_types=1);

it('fails closed before registering API routes unless mandatory authentication middleware is discovered', function (): void {
    $source = (string) file_get_contents(dirname(__DIR__, 3) . '/src/Bootstrap/ApplicationFactory.php');
    expect($source)
        ->toContain('new ModuleApiMiddlewareLoader($modules, $services)')
        ->toContain('instanceof ApiAuthenticationMiddleware')
        ->toContain('API bearer authentication middleware is missing.')
        ->toContain('instanceof ApiSessionAuthenticationMiddleware')
        ->toContain('API session authentication middleware is missing.')
        ->toContain('registerApiRoutes($router, $apiMiddleware, $basePath)');
});
