<?php

declare(strict_types=1);

namespace Zoosper\Auth\Tests\Unit\Http;

use Zoosper\Auth\Http\ApiBearerAuthenticationMiddleware;
use Zoosper\Auth\Token\BearerPrincipalContext;
use Zoosper\Core\Http\Request;
use Zoosper\Core\Http\Response;

it('publishes the authorised principal only during downstream execution', function (): void {
    [$middleware, $service] = bearerMatrixRuntimeWithContext($context);
    $issued = $service->issue(1, 'principal propagation', ['pages:read']);
    $request = new Request('GET', '/api/test', headers: ['authorization' => 'Bearer ' . $issued['token']]);
    $seen = null;
    $response = $middleware->process($request, apiContext('pages:read', []), function (Request $same) use ($context, &$seen): Response {
        $seen = $context->require($same);
        return Response::json(['ok' => true]);
    });
    expect($response->statusCode())->toBe(200)
        ->and($seen)->not->toBeNull()
        ->and($context->has($request))->toBeFalse();
});

it('clears the principal when downstream execution throws', function (): void {
    [$middleware, $service] = bearerMatrixRuntimeWithContext($context);
    $issued = $service->issue(1, 'principal cleanup', ['pages:read']);
    $request = new Request('GET', '/api/test', headers: ['authorization' => 'Bearer ' . $issued['token']]);
    expect(fn () => $middleware->process($request, apiContext('pages:read', []), function (): Response {
        throw new \RuntimeException('downstream failed');
    }))->toThrow(\RuntimeException::class, 'downstream failed');
    expect($context->has($request))->toBeFalse();
});
