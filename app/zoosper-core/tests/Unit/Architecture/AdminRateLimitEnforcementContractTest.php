<?php

declare(strict_types=1);

it('keeps enforcement in Auth middleware and transport headers in Core Response', function (): void {
    $root = dirname(__DIR__, 5);
    $middleware = (string) file_get_contents($root . '/app/zoosper-auth/src/Http/RateLimitReportOnlyAdminMiddleware.php');
    $config = (string) file_get_contents($root . '/app/zoosper-core/config/rate_limit.php');

    expect($middleware)->toContain('if ($config->isReportOnly())')
        ->toContain('$decision = $guard->check($rateLimitContext)')
        ->toContain('Response::raw(')
        ->toContain("'Retry-After' => (string) \$retryAfter")
        ->toContain("'Cache-Control' => 'no-store'")
        ->not->toContain('header(')
        ->and($config)->toContain("RATE_LIMIT_ENABLED")
        ->toContain("RATE_LIMIT_MODE")
        ->toContain("RATE_LIMIT_ADMIN_LOGIN_MAX_ATTEMPTS")
        ->toContain("RATE_LIMIT_ADMIN_LOGIN_WINDOW_SECONDS")
        ->toContain("'mode' => \$mode");
});










