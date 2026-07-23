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
    // Phase 1.39 report-only rate-limit hook. Disabled by default via app/zoosper-core/config/rate_limit.php.
    static function ($request, callable $next) {
        $root = dirname(__DIR__, 3);
        $configPath = $root . '/app/zoosper-core/config/rate_limit.php';
        $config = is_file($configPath) ? require $configPath : [];

        if (! is_array($config) || ($config['enabled'] ?? false) !== true || ($config['mode'] ?? 'report_only') !== 'report_only') {
            return $next($request);
        }

        // Report-only runtime integration is intentionally deferred to the dedicated middleware adapter.
        // This hook proves the admin middleware stack can carry the disabled-by-default seam safely.
        return $next($request);
    },
    AuthenticationMiddleware::class,
    CsrfMiddleware::class,
];