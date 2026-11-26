<?php

declare(strict_types=1);

use Zoosper\Core\Http\Request;
use Zoosper\Core\Http\Response;
use Zoosper\Core\Routing\Router;

it('does not retain state from rejected route registrations', function (): void {
    $router = new Router();
    $handler = fn (): Response => Response::raw('ok');
    $router->map('GET', '/items/{id}', $handler);

    try {
        $router->map('GET', '/items/{name}', $handler, true);
        test()->fail('Expected overlapping route to be rejected');
    } catch (InvalidArgumentException) {
    }

    expect($router->isStateless(new Request('GET', '/items/{name}')))->toBeFalse();
    $router->map('POST', '/items/{name}', $handler);

    try {
        $router->map('GET', '/broken/{id}/{id}', $handler);
        test()->fail('Expected invalid route to be rejected');
    } catch (InvalidArgumentException) {
    }

    expect(fn () => $router->map('GET', '/broken/{id}/{id}', $handler))
        ->toThrow(InvalidArgumentException::class, 'Duplicate route parameter name');
});

it('prefers explicit HEAD handlers and their session policy over GET', function (): void {
    $router = new Router();
    $router->map('GET', '/items/{id}', fn (): Response => Response::raw('get', 200, ['X-Route' => 'get']), true);
    $router->map('HEAD', '/items/{id}', fn (): Response => Response::raw('head', 201, ['X-Route' => 'head']));
    $request = new Request('HEAD', '/items/42');

    expect($router->isStateless($request))->toBeFalse();
    $response = $router->dispatch($request);
    expect($response->statusCode())->toBe(201)
        ->and($response->headers()['X-Route'])->toBe('head')
        ->and($response->body())->toBe('');
});

it('strips bodies from HEAD fallback and not-found responses', function (): void {
    $router = new Router();
    expect($router->dispatch(new Request('HEAD', '/missing'))->body())->toBe('');
    $router->fallback(fn (): Response => Response::raw('fallback', 200, ['X-Test' => 'yes']));
    $response = $router->dispatch(new Request('HEAD', '/missing'));
    expect($response->body())->toBe('')
        ->and($response->headers()['X-Test'])->toBe('yes');
});