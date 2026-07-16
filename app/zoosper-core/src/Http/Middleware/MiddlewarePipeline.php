<?php

declare(strict_types=1);

namespace Zoosper\Core\Http\Middleware;

use Zoosper\Core\Http\Request;
use Zoosper\Core\Http\Response;

/**
 * Runs an ordered list of RouteMiddleware around a core handler (the controller
 * action). The first middleware in the list is the outermost.
 *
 * A middleware that returns a Response without calling $next short-circuits the
 * pipeline - later middleware and the controller are not executed.
 */
final readonly class MiddlewarePipeline
{
    /** @param list<RouteMiddleware> $middleware */
    public function __construct(private array $middleware)
    {
    }

    /**
     * @param callable(Request): Response $core
     */
    public function handle(Request $request, RouteContext $context, callable $core): Response
    {
        $runner = $core;

        foreach (array_reverse($this->middleware) as $middleware) {
            $next = $runner;
            $runner = static fn (Request $req): Response => $middleware->process($req, $context, $next);
        }

        return $runner($request);
    }
}