<?php

declare(strict_types=1);

namespace Zoosper\Core\Tests\Unit\Routing;

use InvalidArgumentException;
use Zoosper\Core\Http\Request;
use Zoosper\Core\Http\Response;
use Zoosper\Core\Routing\Router;

test('exact static routes win over parameterised routes regardless of registration order', function () {
    $router = new Router();
    $matched = null;

    $router->get('/admin/pages/{id}', function (Request $request) use (&$matched): Response {
        $matched = 'parameter:' . $request->routeParam('id');
        return Response::html('parameter');
    });
    $router->get('/admin/pages/create', function () use (&$matched): Response {
        $matched = 'static';
        return Response::html('static');
    });

    $router->dispatch(new Request('GET', '/admin/pages/create'));

    expect($matched)->toBe('static');
});

test('parameterised routes expose a single route parameter on the request', function () {
    $router = new Router();
    $captured = null;

    $router->get('/admin/pages/edit/{id}', function (Request $request) use (&$captured): Response {
        $captured = $request->routeParam('id');
        return Response::html('ok');
    });

    $router->dispatch(new Request('GET', '/admin/pages/edit/123'));

    expect($captured)->toBe('123');
});

test('parameterised routes expose multiple route parameters', function () {
    $router = new Router();
    $captured = [];

    $router->get('/admin/pages/{id}/revisions/{revisionId}', function (Request $request) use (&$captured): Response {
        $captured = $request->routeParams();
        return Response::html('ok');
    });

    $router->dispatch(new Request('GET', '/admin/pages/123/revisions/7'));

    expect($captured)->toBe(['id' => '123', 'revisionId' => '7']);
});

test('inline route parameter constraints accept matching values', function () {
    $router = new Router();
    $captured = null;

    $router->get('/admin/pages/edit/{id:\\d+}', function (Request $request) use (&$captured): Response {
        $captured = $request->routeParam('id');
        return Response::html('ok');
    });

    $router->dispatch(new Request('GET', '/admin/pages/edit/42'));

    expect($captured)->toBe('42');
});

test('inline route parameter constraints reject non matching values', function () {
    $router = new Router();
    $matched = false;
    $fallback = false;

    $router->get('/admin/pages/edit/{id:\\d+}', function () use (&$matched): Response {
        $matched = true;
        return Response::html('matched');
    });
    $router->fallback(function () use (&$fallback): Response {
        $fallback = true;
        return Response::html('fallback', 404);
    });

    $router->dispatch(new Request('GET', '/admin/pages/edit/not-a-number'));

    expect($matched)->toBeFalse();
    expect($fallback)->toBeTrue();
});

test('default route parameters do not consume multiple path segments', function () {
    $router = new Router();
    $matched = false;
    $fallback = false;

    $router->get('/files/{path}', function () use (&$matched): Response {
        $matched = true;
        return Response::html('matched');
    });
    $router->fallback(function () use (&$fallback): Response {
        $fallback = true;
        return Response::html('fallback', 404);
    });

    $router->dispatch(new Request('GET', '/files/a/b'));

    expect($matched)->toBeFalse();
    expect($fallback)->toBeTrue();
});

test('trailing slash paths are normalised before parameter matching', function () {
    $router = new Router();
    $captured = null;

    $router->get('/admin/pages/edit/{id}', function (Request $request) use (&$captured): Response {
        $captured = $request->routeParam('id');
        return Response::html('ok');
    });

    $router->dispatch(new Request('GET', '/admin/pages/edit/123/'));

    expect($captured)->toBe('123');
});

test('invalid parameter segments are rejected when routes are registered', function () {
    $router = new Router();

    expect(fn () => $router->get('/admin/pages/{bad-name}', static fn (): Response => Response::html('bad')))
        ->toThrow(InvalidArgumentException::class);
});
