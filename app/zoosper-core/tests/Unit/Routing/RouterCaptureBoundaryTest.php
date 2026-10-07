<?php

declare(strict_types=1);

use Zoosper\Core\Http\Request;
use Zoosper\Core\Http\Response;
use Zoosper\Core\Routing\Router;

/** Preserve named regex capture decoding through public dispatch. */
it('preserves string route captures and decodes exactly once', function (string $encoded, string $expected): void {
    $router = new Router();
    $router->get('/capture/{value}/end', static fn (Request $request): Response => Response::html($request->routeParam('value') ?? 'missing'));
    $request = new Request('GET', '/capture/' . $encoded . '/end');
    $response = $router->dispatch($request);

    expect($response->statusCode())->toBe(200)
        ->and($response->body())->toBe($expected)
        ->and($request->routeParams())->toBe([]);
})->with([
    'zero' => ['0', '0'],
    'plain' => ['alpha', 'alpha'],
    'space' => ['hello%20world', 'hello world'],
    'plus stays plus' => ['a+b', 'a+b'],
    'encoded slash' => ['a%2Fb', 'a/b'],
    'single decoding' => ['%252F', '%2F'],
]);

/** A constrained empty capture is distinct from a missing default segment. */
it('preserves empty constrained captures', function (): void {
    $router = new Router();
    $router->get('/capture/{value:.*}/end', static fn (Request $request): Response => Response::html($request->routeParam('value') ?? 'missing'));
    $response = $router->dispatch(new Request('GET', '/capture//end'));
    expect($response->statusCode())->toBe(200)->and($response->body())->toBe('');
});

/** Reusing a router must not leak captured values into later requests. */
it('isolates route captures across dispatches', function (): void {
    $router = new Router();
    $router->get('/capture/{value}', static fn (Request $request): Response => Response::html($request->routeParam('value') ?? 'missing'));
    $router->get('/static', static fn (Request $request): Response => Response::html($request->routeParam('value') ?? 'missing'));
    expect($router->dispatch(new Request('GET', '/capture/0'))->body())->toBe('0')
        ->and($router->dispatch(new Request('GET', '/capture/second'))->body())->toBe('second')
        ->and($router->dispatch(new Request('GET', '/static'))->body())->toBe('missing');
});
