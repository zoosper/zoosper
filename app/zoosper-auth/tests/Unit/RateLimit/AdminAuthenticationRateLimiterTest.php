<?php

declare(strict_types=1);

use Zoosper\Auth\RateLimit\AdminAuthenticationRateLimiter;

function lifecycleRateLimitBase(string $mode = 'enforce'): string
{
    $base = sys_get_temp_dir() . '/zoosper-auth-rate-' . bin2hex(random_bytes(4));
    mkdir($base . '/app/zoosper-core/config', 0777, true);
    $config = [
        'enabled' => true,
        'mode' => $mode,
        'report_path' => 'var/reports/rate.jsonl',
        'identity_salt' => str_repeat('c', 64),
        'policies' => [
            'admin.login' => ['scope' => 'admin', 'max_attempts' => 1, 'window_seconds' => 300],
            'admin.two_factor' => ['scope' => 'admin', 'max_attempts' => 1, 'window_seconds' => 300],
        ],
    ];
    file_put_contents($base . '/app/zoosper-core/config/rate_limit.php', '<?php return ' . var_export($config, true) . ';');
    return $base;
}

it('enforces and resets a separate two-factor bucket', function (): void {
    $pdo = new PDO('sqlite::memory:');
    $limiter = new AdminAuthenticationRateLimiter($pdo, lifecycleRateLimitBase());

    expect($limiter->checkTwoFactor(7, '203.0.113.2')->allowed)->toBeTrue()
        ->and($limiter->checkTwoFactor(7, '203.0.113.2')->allowed)->toBeFalse();

    $limiter->resetTwoFactor(7, '203.0.113.2');
    expect($limiter->checkTwoFactor(7, '203.0.113.2')->allowed)->toBeTrue();
});

it('keeps report-only two-factor checks non-blocking', function (): void {
    $pdo = new PDO('sqlite::memory:');
    $limiter = new AdminAuthenticationRateLimiter($pdo, lifecycleRateLimitBase('report_only'));

    expect($limiter->checkTwoFactor(9, '203.0.113.3')->allowed)->toBeTrue()
        ->and($limiter->checkTwoFactor(9, '203.0.113.3')->allowed)->toBeTrue();
});










