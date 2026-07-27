<?php

declare(strict_types=1);

use Zoosper\Core\Asset\AssetController;
use Zoosper\Core\Asset\AssetModuleRegistry;
use Zoosper\Core\Asset\AssetResolver;
use Zoosper\Core\Asset\AssetRouteRegistrar;
use Zoosper\Core\Http\Request;
use Zoosper\Core\Routing\Router;

/*
 * Phase C1 behavioural test: registers the EXACT production route (via
 * AssetRouteRegistrar::register(), the same class ApplicationFactory calls -
 * no duplicated/rewritten logic that could drift from production) against a
 * real Router, pointed at a genuine temp module-assets fixture, and dispatches
 * real Request objects through it. Proves the whole chain end-to-end:
 * Router -> route match -> AssetController::serve() -> Response.
 */

function removeAssetRouteFixtureDir(string $dir): void
{
    if (!is_dir($dir)) {
        return;
    }
    $items = scandir($dir) ?: [];
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }
        $path = $dir . DIRECTORY_SEPARATOR . $item;
        is_dir($path) ? removeAssetRouteFixtureDir($path) : unlink($path);
    }
    rmdir($dir);
}

/** Build a temp module assets directory with a real CSS file inside. */
function makeAssetRouteFixture(): array
{
    $base = sys_get_temp_dir() . '/zoosper-asset-route-test-' . bin2hex(random_bytes(8));
    $assetsDir = $base . '/css';
    mkdir($assetsDir, 0775, true);
    $cssContent = ".grid-table { width: 100%; }\n";
    file_put_contents($assetsDir . '/style.css', $cssContent);

    return [$base, $cssContent];
}

/**
 * Build a Request for a given path with the given headers.
 *
 * Request's constructor is public (confirmed from source), so it is
 * constructed directly — no reflection needed here.
 */
function makeAssetRequest(string $path, array $headers = []): Request
{
    return new Request(
        method: 'GET',
        path: $path,
        headers: array_change_key_case($headers, CASE_LOWER),
        body: '',
        query: [],
        host: 'localhost',
        clientIp: '127.0.0.1',
    );
}

function buildAssetRouterFor(string $moduleBaseDir): Router
{
    $registry = new AssetModuleRegistry(['test-module' => $moduleBaseDir]);
    $controller = new AssetController(new AssetResolver($registry));

    $router = new Router();
    AssetRouteRegistrar::register($router, $controller);

    return $router;
}

it('serves a real module asset over the registered route with correct headers', function (): void {
    [$base, $cssContent] = makeAssetRouteFixture();

    try {
        $router = buildAssetRouterFor($base);
        $request = makeAssetRequest('/asset/test-module/css/style.css');

        $response = $router->dispatch($request);

        $reflection = new ReflectionClass($response);
        $bodyProp = $reflection->getProperty('body');
        $bodyProp->setAccessible(true);
        $statusProp = $reflection->getProperty('statusCode');
        $statusProp->setAccessible(true);
        $headersProp = $reflection->getProperty('headers');
        $headersProp->setAccessible(true);

        expect($statusProp->getValue($response))->toBe(200)
            ->and($bodyProp->getValue($response))->toBe($cssContent)
            ->and($headersProp->getValue($response)['Content-Type'])->toBe('text/css')
            ->and($headersProp->getValue($response))->toHaveKey('ETag')
            ->and($headersProp->getValue($response)['Cache-Control'])->toContain('immutable');
    } finally {
        removeAssetRouteFixtureDir($base);
    }
});

it('returns 404 for an unknown module', function (): void {
    [$base] = makeAssetRouteFixture();

    try {
        $router = buildAssetRouterFor($base);
        $request = makeAssetRequest('/asset/nonexistent-module/css/style.css');

        $response = $router->dispatch($request);

        $reflection = new ReflectionClass($response);
        $statusProp = $reflection->getProperty('statusCode');
        $statusProp->setAccessible(true);

        expect($statusProp->getValue($response))->toBe(404);
    } finally {
        removeAssetRouteFixtureDir($base);
    }
});

it('returns 404 for a path-traversal attempt', function (): void {
    [$base] = makeAssetRouteFixture();

    try {
        $router = buildAssetRouterFor($base);
        // URL-encoded traversal in the path segment.
        $request = makeAssetRequest('/asset/test-module/..%2f..%2fetc%2fpasswd');

        $response = $router->dispatch($request);

        $reflection = new ReflectionClass($response);
        $statusProp = $reflection->getProperty('statusCode');
        $statusProp->setAccessible(true);

        expect($statusProp->getValue($response))->toBe(404);
    } finally {
        removeAssetRouteFixtureDir($base);
    }
});

it('returns 304 when If-None-Match matches the current ETag', function (): void {
    [$base] = makeAssetRouteFixture();

    try {
        $router = buildAssetRouterFor($base);

        $first = $router->dispatch(makeAssetRequest('/asset/test-module/css/style.css'));

        $reflection = new ReflectionClass($first);
        $headersProp = $reflection->getProperty('headers');
        $headersProp->setAccessible(true);
        $etag = $headersProp->getValue($first)['ETag'];

        $second = $router->dispatch(makeAssetRequest('/asset/test-module/css/style.css', ['If-None-Match' => $etag]));

        $statusProp = $reflection->getProperty('statusCode');
        $statusProp->setAccessible(true);
        $bodyProp = $reflection->getProperty('body');
        $bodyProp->setAccessible(true);

        expect($statusProp->getValue($second))->toBe(304)
            ->and($bodyProp->getValue($second))->toBe('');
    } finally {
        removeAssetRouteFixtureDir($base);
    }
});

it('supports nested subdirectory paths (js/vendor/deep/file.js style)', function (): void {
    $base = sys_get_temp_dir() . '/zoosper-asset-route-nested-' . bin2hex(random_bytes(8));
    $nestedDir = $base . '/js/vendor';
    mkdir($nestedDir, 0775, true);
    file_put_contents($nestedDir . '/lib.js', "console.log('ok');\n");

    try {
        $router = buildAssetRouterFor($base);
        $response = $router->dispatch(makeAssetRequest('/asset/test-module/js/vendor/lib.js'));

        $reflection = new ReflectionClass($response);
        $statusProp = $reflection->getProperty('statusCode');
        $statusProp->setAccessible(true);
        $headersProp = $reflection->getProperty('headers');
        $headersProp->setAccessible(true);

        expect($statusProp->getValue($response))->toBe(200)
            ->and($headersProp->getValue($response)['Content-Type'])->toBe('text/javascript');
    } finally {
        removeAssetRouteFixtureDir($base);
    }
});
