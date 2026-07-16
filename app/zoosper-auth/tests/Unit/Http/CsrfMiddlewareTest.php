<?php

declare(strict_types=1);

namespace Zoosper\Auth\Tests\Unit\Http;

use Zoosper\Auth\Http\CsrfMiddleware;
use Zoosper\Auth\Service\CsrfTokenManager;
use Zoosper\Core\Http\Middleware\RouteContext;
use Zoosper\Core\Http\Request;
use Zoosper\Core\Http\Response;

function csrfContext(): RouteContext
{
    return new RouteContext('POST', '/admin/pages/publish', false, 'page.manage');
}

test('a GET request bypasses CSRF validation', function () {
    $_SESSION = [];
    $middleware = new CsrfMiddleware(new CsrfTokenManager());

    $reached = false;
    $middleware->process(new Request('GET', '/admin/pages'), new RouteContext('GET', '/admin/pages', false, 'page.manage'), function () use (&$reached): Response {
        $reached = true;

        return Response::html('ok', 200);
    });

    expect($reached)->toBeTrue();
});

test('a POST with a valid token passes through', function () {
    $_SESSION = [];
    $csrf = new CsrfTokenManager();
    $_POST = ['_csrf_token' => $csrf->token()];
    $middleware = new CsrfMiddleware($csrf);

    $response = $middleware->process(new Request('POST', '/admin/pages/publish'), csrfContext(), static fn (Request $request): Response => Response::html('ok', 200));

    expect($response)->toBeInstanceOf(Response::class);
    $_POST = [];
});

test('a POST with an invalid token is blocked before the handler', function () {
    $_SESSION = [];
    $csrf = new CsrfTokenManager();
    $csrf->token();
    $_POST = ['_csrf_token' => 'wrong-token'];
    $middleware = new CsrfMiddleware($csrf);

    $reached = false;
    $middleware->process(new Request('POST', '/admin/pages/publish'), csrfContext(), function () use (&$reached): Response {
        $reached = true;

        return Response::html('ok', 200);
    });

    expect($reached)->toBeFalse();
    $_POST = [];
});