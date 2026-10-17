<?php

declare(strict_types=1);

namespace Zoosper\Core\Http\Middleware;

/** Marker contract for the mandatory API authentication and authorisation guard. */
interface ApiAuthenticationMiddleware extends RouteMiddleware
{
}
