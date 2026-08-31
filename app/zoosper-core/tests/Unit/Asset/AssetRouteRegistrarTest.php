<?php

declare(strict_types=1);

use Zoosper\Core\Asset\AssetController;
use Zoosper\Core\Asset\AssetModuleRegistry;
use Zoosper\Core\Asset\AssetResolver;
use Zoosper\Core\Asset\AssetRouteRegistrar;
use Zoosper\Core\Http\Request;
use Zoosper\Core\Routing\Router;

/*
 * Phase C1/C2 behavioural test: registers the EXACT production route (via
 * AssetRouteRegistrar::register() — the same class ApplicationFactory calls,
 * no duplicated/rewritten logic that could drift) against a real Router,
 * pointed at a genuine temp module-assets fixture, and dispatches real
 * Request objects through it. Proves the whole chain end-to-end: Router ->
 * route match -> AssetController::serve() -> Response.
 *
 * Phase C2 fix: ReflectionProperty::setAccessible() calls were removed
 * (deprecated since PHP 8.1, no-op since then, and now emits a deprecation
 * warning on PHP 8.5 — properties obtained via Reflection are accessible by
 * default in modern PHP, so the calls were pure dead weight).
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
 * Build a Request for a given path/method with the given headers. Request's
 * constructor is public (confirmed from source), so it is constructed
 * directly — no reflection needed here.
 */
function makeAssetRequest(string $path, array $headers = [], string $method = 'GET'): Request
{
    return new Request(
        method: $method,
        path: $path,
        headers: array_change_key_case($headers, CASE_LOWER),
        body: '',
        query: [],
        host: 'localhost',
        clientIp: '127.0.0.1',
    );
}

function buildAssetRouterFor(string $moduleBaseDir, int $cacheMaxAge = 31536000, bool $cacheImmutable = true): Router
{
    $registry = new AssetModuleRegistry(['test-module' => $moduleBaseDir]);
    $controller = new AssetController(new AssetResolver($registry), $cacheMaxAge, $cacheImmutable);

    $router = new Router();
    AssetRouteRegistrar::register($router, $controller);

    return $router;
}

/**
 * Extract the private status/body/headers from a Response without
 * ReflectionProperty::setAccessible() (unneeded and deprecated on PHP 8.1+).
 *
 * @return array{status: int, body: string, headers: array<string, string>}
 */
function inspectResponse(\Zoosper\Core\Http\Response $response): array
{
    $reflection = new ReflectionClass($response);

    return [
        'status' => $reflection->getProperty('statusCode')->getValue($response),
        'body' => $reflection->getProperty('body')->getValue($response),
        'headers' => $reflection->getProperty('headers')->getValue($response),
    ];
}

it('serves a real module asset over the registered route with correct headers', function (): void {
    [$base, $cssContent] = makeAssetRouteFixture();

    try {
        $router = buildAssetRouterFor($base);
        $response = $router->dispatch(makeAssetRequest('/asset/test-module/css/style.css'));
        $r = inspectResponse($response);

        expect($r['status'])->toBe(200)
            ->and($r['body'])->toBe($cssContent)
            ->and($r['headers']['Content-Type'])->toBe('text/css')
            ->and($r['headers'])->toHaveKey('ETag')
            ->and($r['headers']['Cache-Control'])->toContain('immutable');
    } finally {
        removeAssetRouteFixtureDir($base);
    }
});

it('returns 404 for an unknown module', function (): void {
    [$base] = makeAssetRouteFixture();

    try {
        $router = buildAssetRouterFor($base);
        $response = $router->dispatch(makeAssetRequest('/asset/nonexistent-module/css/style.css'));

        expect(inspectResponse($response)['status'])->toBe(404);
    } finally {
        removeAssetRouteFixtureDir($base);
    }
});

it('returns 404 for a path-traversal attempt', function (): void {
    [$base] = makeAssetRouteFixture();

    try {
        $router = buildAssetRouterFor($base);
        // URL-encoded traversal in the path segment.
        $response = $router->dispatch(makeAssetRequest('/asset/test-module/..%2f..%2fetc%2fpasswd'));

        expect(inspectResponse($response)['status'])->toBe(404);
    } finally {
        removeAssetRouteFixtureDir($base);
    }
});

it('returns 304 when If-None-Match matches the current ETag', function (): void {
    [$base] = makeAssetRouteFixture();

    try {
        $router = buildAssetRouterFor($base);

        $first = inspectResponse($router->dispatch(makeAssetRequest('/asset/test-module/css/style.css')));
        $etag = $first['headers']['ETag'];

        $second = inspectResponse($router->dispatch(makeAssetRequest(
            '/asset/test-module/css/style.css',
            ['If-None-Match' => $etag],
        )));

        expect($second['status'])->toBe(304)
            ->and($second['body'])->toBe('');
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
        $r = inspectResponse($response);

        expect($r['status'])->toBe(200)
            ->and($r['headers']['Content-Type'])->toBe('text/javascript');
    } finally {
        removeAssetRouteFixtureDir($base);
    }
});

// --- Phase C2: configurable TTL / immutable flag ---

it('reflects a CUSTOM cache_max_age in the Cache-Control header', function (): void {
    [$base] = makeAssetRouteFixture();

    try {
        $router = buildAssetRouterFor($base, cacheMaxAge: 3600, cacheImmutable: true);
        $r = inspectResponse($router->dispatch(makeAssetRequest('/asset/test-module/css/style.css')));

        expect($r['headers']['Cache-Control'])->toBe('public, max-age=3600, immutable');
    } finally {
        removeAssetRouteFixtureDir($base);
    }
});

it('omits the immutable directive when cache_immutable is false', function (): void {
    [$base] = makeAssetRouteFixture();

    try {
        $router = buildAssetRouterFor($base, cacheMaxAge: 600, cacheImmutable: false);
        $r = inspectResponse($router->dispatch(makeAssetRequest('/asset/test-module/css/style.css')));

        expect($r['headers']['Cache-Control'])->toBe('public, max-age=600')
            ->and($r['headers']['Cache-Control'])->not->toContain('immutable');
    } finally {
        removeAssetRouteFixtureDir($base);
    }
});

// --- Phase C2: HEAD support ---

it('responds to HEAD with the SAME headers as GET but an EMPTY body', function (): void {
    [$base, $cssContent] = makeAssetRouteFixture();

    try {
        $router = buildAssetRouterFor($base);

        $get = inspectResponse($router->dispatch(makeAssetRequest('/asset/test-module/css/style.css', [], 'GET')));
        $head = inspectResponse($router->dispatch(makeAssetRequest('/asset/test-module/css/style.css', [], 'HEAD')));

        expect($get['status'])->toBe(200)
            ->and($get['body'])->toBe($cssContent)
            ->and($head['status'])->toBe(200)
            ->and($head['body'])->toBe('') // MUST NOT include a body (RFC 9110 9.3.2)
            // MUST have the SAME headers as the equivalent GET response.
            ->and($head['headers'])->toBe($get['headers']);
    } finally {
        removeAssetRouteFixtureDir($base);
    }
});

it('HEAD also returns 404 for an unknown module, matching GET', function (): void {
    [$base] = makeAssetRouteFixture();

    try {
        $router = buildAssetRouterFor($base);
        $response = $router->dispatch(makeAssetRequest('/asset/nonexistent-module/css/style.css', [], 'HEAD'));

        expect(inspectResponse($response)['status'])->toBe(404);
    } finally {
        removeAssetRouteFixtureDir($base);
    }
});










