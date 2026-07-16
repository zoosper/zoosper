<?php

declare(strict_types=1);

namespace Zoosper\Core\Http\Middleware;

use Zoosper\Core\Http\Request;
use Zoosper\Core\Http\Response;

/**
 * A PSR-15-style route middleware.
 *
 * A middleware either returns a Response itself (short-circuit) or calls
 * $next($request) to pass control inward, eventually reaching the controller.
 */
interface RouteMiddleware
{
    /**
     * @param callable(Request): Response $next
     */
    public function process(Request $request, RouteContext $context, callable $next): Response;
}