<?php

declare(strict_types=1);

namespace Zoosper\Core\Http\Middleware;

/**
 * Marker contract for the mandatory Admin authentication guard.
 *
 * Core owns only the composition requirement. The Auth module owns the
 * implementation and its session and permission semantics.
 */
interface AdminAuthenticationMiddleware extends RouteMiddleware
{
}
