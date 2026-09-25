<?php

declare(strict_types=1);

it('keeps password and two-factor rate-limit lifecycle interface-owned and policy-separated', function (): void {
    $root = dirname(__DIR__, 5);
    $login = (string) file_get_contents($root . '/app/zoosper-admin/src/Controller/LoginController.php');
    $challenge = (string) file_get_contents($root . '/app/zoosper-two-factor/src/Controller/AdminTwoFactorChallengeController.php');
    $config = (string) file_get_contents($root . '/app/zoosper-core/config/rate_limit.php');

    expect($login)->toContain('AdminAuthenticationRateLimiterInterface')
        ->toContain('resetPasswordLogin($user->email, $request->clientIp())')
        ->not->toContain('DatabaseRateLimitStore')
        ->and($challenge)->toContain('checkTwoFactor($userId, $request->clientIp())')
        ->toContain('resetTwoFactor($user->id, $request->clientIp())')
        ->not->toContain('DatabaseRateLimitStore')
        ->and($config)->toContain("'admin.login.subject'")
        ->toContain("'admin.login.pair'")
        ->toContain("'admin.login.ip'")
        ->toContain("'admin.two_factor.subject'")
        ->toContain("'admin.two_factor.pair'")
        ->toContain("'admin.two_factor.ip'")
        ->toContain('RATE_LIMIT_ADMIN_TWO_FACTOR_MAX_ATTEMPTS')
        ->toContain('RATE_LIMIT_ADMIN_TWO_FACTOR_WINDOW_SECONDS');
});










