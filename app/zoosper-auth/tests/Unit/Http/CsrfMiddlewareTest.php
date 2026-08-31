<?php

declare(strict_types=1);

namespace Zoosper\Auth\Tests\Unit\Http;

use Zoosper\Auth\Http\CsrfMiddleware;
use Zoosper\Auth\Service\CsrfTokenManager;
use Zoosper\Core\Http\Middleware\RouteContext;
use Zoosper\Core\Http\Request;
use Zoosper\Core\Http\Response;

/**
 * UPDATED (2026-07-30, alongside the Request::form() immutability fix):
 * previously this test relied on Request::form() reading the live $_POST
 * superglobal directly, so it constructed a bare `new Request('POST', ...)`
 * with no form data at all, then separately mutated global $_POST around
 * the call. Now that Request::form() reads from a genuinely immutable
 * constructor-provided property (see Request.php), form data is passed
 * directly into the Request constructor instead — no global state mutation
 * needed, and no more risk of test pollution if tests run out of order or
 * in parallel.
 */
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
    $token = $csrf->token();
    $middleware = new CsrfMiddleware($csrf);

    $request = new Request('POST', '/admin/pages/publish', form: ['_csrf_token' => $token]);
    $response = $middleware->process($request, csrfContext(), static fn (Request $request): Response => Response::html('ok', 200));

    expect($response)->toBeInstanceOf(Response::class);
});

test('a POST with an invalid token is blocked before the handler', function () {
    $_SESSION = [];
    $csrf = new CsrfTokenManager();
    $csrf->token();
    $middleware = new CsrfMiddleware($csrf);

    $request = new Request('POST', '/admin/pages/publish', form: ['_csrf_token' => 'wrong-token']);

    $reached = false;
    $middleware->process($request, csrfContext(), function () use (&$reached): Response {
        $reached = true;

        return Response::html('ok', 200);
    });

    expect($reached)->toBeFalse();
});










