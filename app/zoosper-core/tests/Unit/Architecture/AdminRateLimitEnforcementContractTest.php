<?php

declare(strict_types=1);

it('keeps authentication policy execution canonical and HTTP transport separate', function (): void {
    $root = dirname(__DIR__, 5);

    $limiter = (string) file_get_contents(
        $root
        . '/app/zoosper-auth/src/RateLimit/'
        . 'AdminAuthenticationRateLimiter.php',
    );
    $middleware = (string) file_get_contents(
        $root
        . '/app/zoosper-auth/src/Http/'
        . 'RateLimitReportOnlyAdminMiddleware.php',
    );
    $services = (string) file_get_contents(
        $root . '/app/zoosper-auth/config/services.php',
    );
    $config = (string) file_get_contents(
        $root . '/app/zoosper-core/config/rate_limit.php',
    );

    expect($limiter)
        ->toContain('new DatabaseRateLimitStore(')
        ->toContain('new FileRateLimitReportSink(')
        ->toContain('RateLimitReportEvent::fromDecision(')
        ->toContain("return \$this->check('admin.login'")
        ->toContain("return \$this->check('admin.two_factor'")
        ->toContain(
            "return \$this->check("
            . "'admin.password_reset_request'"
        )
        ->and($middleware)
        ->toContain('AdminAuthenticationRateLimiterInterface')
        ->toContain('checkPasswordLogin(')
        ->toContain('Response::raw(')
        ->toContain("'Retry-After'")
        ->toContain("'Cache-Control' => 'no-store'")
        ->not->toContain('use PDO;')
        ->not->toContain('DatabaseRateLimitStore')
        ->not->toContain('AdminRateLimitContextFactory')
        ->not->toContain('RateLimitIdentityHasher')
        ->not->toContain('RateLimitRuntimeConfig')
        ->not->toContain('FileRateLimitReportSink')
        ->not->toContain('RateLimitGuard')
        ->not->toContain('RateLimitEnforcer')
        ->and($services)
        ->toContain(
            '$services->get('
            . 'AdminAuthenticationRateLimiterInterface::class),'
        )
        ->and($config)
        ->toContain('RATE_LIMIT_ENABLED')
        ->toContain('RATE_LIMIT_MODE')
        ->toContain(
            'RATE_LIMIT_ADMIN_LOGIN_MAX_ATTEMPTS'
        )
        ->toContain(
            'RATE_LIMIT_ADMIN_LOGIN_WINDOW_SECONDS'
        );
});