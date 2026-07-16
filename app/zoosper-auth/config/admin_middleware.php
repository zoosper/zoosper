<?php

declare(strict_types=1);

use Zoosper\Auth\Http\AuthenticationMiddleware;
use Zoosper\Auth\Http\CsrfMiddleware;

/**
 * Admin route middleware stack (outermost first).
 *
 * Authentication runs before CSRF so an unauthenticated request is redirected
 * to login rather than shown a 419.
 */
return [
    AuthenticationMiddleware::class,
    CsrfMiddleware::class,
];