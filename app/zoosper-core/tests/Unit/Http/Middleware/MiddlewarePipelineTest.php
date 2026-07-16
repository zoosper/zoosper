<?php

declare(strict_types=1);

namespace Zoosper\Core\Tests\Unit\Http\Middleware;

use Zoosper\Core\Http\Middleware\MiddlewarePipeline;
use Zoosper\Core\Http\Middleware\RouteContext;
use Zoosper\Core\Http\Middleware\RouteMiddleware;
use Zoosper\Core\Http\Request;
use Zoosper\Core\Http\Response;

/** @param list<string> $order */
function recordingMiddleware(string $label, array &$order): RouteMiddleware
{
    return new class ($label, $order) implements RouteMiddleware {
        /** @param list<string> $order */
        public function __construct(private string $label, private array &$order)
        {
        }

        public function process(Request $request, RouteContext $context, callable $next): Response
        {
            $this->order[] = 'before:' . $this->label;
            $response = $next($request);
            $this->order[] = 'after:' . $this->label;

            return $response;
        }
    };
}

function shortCircuitMiddleware(int $status): RouteMiddleware
{
    return new class ($status) implements RouteMiddleware {
        public function __construct(private int $status)
        {
        }

        public function process(Request $request, RouteContext $context, callable $next): Response
        {
            return Response::html('blocked', $this->status);
        }
    };
}

function makeMiddlewareRequest(): Request
{
    return new Request('GET', '/admin/pages');
}

function makeMiddlewareContext(): RouteContext
{
    return new RouteContext('GET', '/admin/pages', false, 'page.manage');
}

test('runs middleware as an onion around the core handler', function () {
    $order = [];
    $pipeline = new MiddlewarePipeline([
        recordingMiddleware('outer', $order),
        recordingMiddleware('inner', $order),
    ]);

    $pipeline->handle(makeMiddlewareRequest(), makeMiddlewareContext(), function () use (&$order): Response {
        $order[] = 'core';

        return Response::html('ok', 200);
    });

    expect($order)->toBe(['before:outer', 'before:inner', 'core', 'after:inner', 'after:outer']);
});

test('an empty pipeline calls the core handler directly', function () {
    $pipeline = new MiddlewarePipeline([]);
    $called = false;

    $pipeline->handle(makeMiddlewareRequest(), makeMiddlewareContext(), function () use (&$called): Response {
        $called = true;

        return Response::html('ok', 200);
    });

    expect($called)->toBeTrue();
});

test('a short-circuiting middleware stops the core handler running', function () {
    $pipeline = new MiddlewarePipeline([shortCircuitMiddleware(419)]);
    $coreRan = false;

    $pipeline->handle(makeMiddlewareRequest(), makeMiddlewareContext(), function () use (&$coreRan): Response {
        $coreRan = true;

        return Response::html('ok', 200);
    });

    expect($coreRan)->toBeFalse();
});