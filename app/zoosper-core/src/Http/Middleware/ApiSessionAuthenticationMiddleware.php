<?php

declare(strict_types=1);

namespace Zoosper\Core\Http\Middleware;

/** Marker contract for the mandatory session-authenticated API guard. */
interface ApiSessionAuthenticationMiddleware extends RouteMiddleware
{
}
