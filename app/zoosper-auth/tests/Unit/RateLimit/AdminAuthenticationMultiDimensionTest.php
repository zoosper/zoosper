<?php

declare(strict_types=1);

use Zoosper\Auth\RateLimit\AdminAuthenticationRateLimiter;

function multiDimensionBase(string $mode = 'enforce'): string
{
    $base = sys_get_temp_dir() . '/zoosper-auth-multidimension-' . bin2hex(random_bytes(5));
    mkdir($base . '/app/zoosper-core/config', 0777, true);
    $rule = ['scope' => 'admin', 'max_attempts' => 2, 'window_seconds' => 300];
    $policies = ['admin.password_reset_request' => $rule];
    foreach (['admin.login', 'admin.two_factor'] as $operation) {
        foreach (['subject', 'pair', 'ip'] as $dimension) {
            $policies[$operation . '.' . $dimension] = $rule;
        }
    }
    file_put_contents($base . '/app/zoosper-core/config/rate_limit.php', '<?php return ' . var_export([
        'enabled' => true,
        'mode' => $mode,
        'report_path' => 'var/reports/rate.jsonl',
        'identity_salt' => str_repeat('m', 64),
        'policies' => $policies,
    ], true) . ';');

    return $base;
}

function multiDimensionLimiter(string $mode = 'enforce'): array
{
    $pdo = new PDO('sqlite::memory:');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    return [$pdo, new AdminAuthenticationRateLimiter($pdo, multiDimensionBase($mode))];
}

it('blocks password spraying across different accounts from one client IP', function (): void {
    [, $limiter] = multiDimensionLimiter();
    expect($limiter->checkPasswordLogin('one@example.test', '203.0.113.40')->allowed)->toBeTrue()
        ->and($limiter->checkPasswordLogin('two@example.test', '203.0.113.40')->allowed)->toBeTrue()
        ->and($limiter->checkPasswordLogin('three@example.test', '203.0.113.40')->allowed)->toBeFalse();
});

it('keeps independent IP addresses and accounts isolated', function (): void {
    [, $limiter] = multiDimensionLimiter();
    expect($limiter->checkPasswordLogin('one@example.test', '203.0.113.41')->allowed)->toBeTrue()
        ->and($limiter->checkPasswordLogin('two@example.test', '203.0.113.42')->allowed)->toBeTrue()
        ->and($limiter->checkPasswordLogin('one@example.test', '203.0.113.43')->allowed)->toBeTrue();
});

it('clears successful password subject and pair buckets without erasing shared IP abuse history', function (): void {
    [$pdo, $limiter] = multiDimensionLimiter();
    $limiter->checkPasswordLogin('one@example.test', '203.0.113.44');
    $limiter->checkPasswordLogin('two@example.test', '203.0.113.44');
    $limiter->resetPasswordLogin('one@example.test', '203.0.113.44');
    expect((int) $pdo->query("SELECT COUNT(*) FROM rate_limit_buckets WHERE rule_key='admin.login.ip'")->fetchColumn())->toBe(1)
        ->and($limiter->checkPasswordLogin('three@example.test', '203.0.113.44')->allowed)->toBeFalse();
});

it('does not create a shared empty client-IP dimension when client IP is unavailable', function (): void {
    [$pdo, $limiter] = multiDimensionLimiter();
    $limiter->checkPasswordLogin('one@example.test', null);
    $limiter->checkPasswordLogin('two@example.test', null);
    expect((int) $pdo->query("SELECT COUNT(*) FROM rate_limit_buckets WHERE rule_key='admin.login.ip'")->fetchColumn())->toBe(0)
        ->and($limiter->checkPasswordLogin('three@example.test', null)->allowed)->toBeTrue();
});

it('applies the same subject pair and IP boundaries to two-factor challenges', function (): void {
    [, $limiter] = multiDimensionLimiter();
    expect($limiter->checkTwoFactor(7, '203.0.113.45')->allowed)->toBeTrue()
        ->and($limiter->checkTwoFactor(8, '203.0.113.45')->allowed)->toBeTrue()
        ->and($limiter->checkTwoFactor(9, '203.0.113.45')->allowed)->toBeFalse();
});

it('records every dimension but remains non-blocking in report-only mode', function (): void {
    [$pdo, $limiter] = multiDimensionLimiter('report_only');
    for ($i = 0; $i < 3; $i++) {
        expect($limiter->checkPasswordLogin('report@example.test', '203.0.113.46')->allowed)->toBeTrue();
    }
    expect((int) $pdo->query("SELECT COUNT(*) FROM rate_limit_buckets WHERE rule_key LIKE 'admin.login.%'")->fetchColumn())->toBe(3);
});
