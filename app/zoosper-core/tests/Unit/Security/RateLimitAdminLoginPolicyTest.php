<?php

declare(strict_types=1);

use Zoosper\Core\Security\RateLimit\AdminRateLimitContextFactory;
use Zoosper\Core\Security\RateLimit\RateLimitIdentityHasher;
use Zoosper\Core\Security\RateLimit\RateLimitRuntimeConfig;

use function PHPUnit\Framework\assertFileExists;
use function PHPUnit\Framework\assertMatchesRegularExpression;
use function PHPUnit\Framework\assertSame;
use function PHPUnit\Framework\assertStringContainsString;
use function PHPUnit\Framework\fail;

$repoRootPath = static function (): string {
    $current = __DIR__;
    while ($current !== dirname($current)) {
        if (is_file($current . DIRECTORY_SEPARATOR . 'composer.json') && is_dir($current . DIRECTORY_SEPARATOR . 'app')) {
            return $current;
        }
        $current = dirname($current);
    }
    fail('Unable to locate Zoosper repository root from ' . __DIR__);
};

$rootPath = static function (string $path = '') use ($repoRootPath): string {
    $root = $repoRootPath();
    return $path === '' ? $root : $root . DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR);
};

it('documents admin login report-only policy rollout', function () use ($rootPath): void {
    assertFileExists($rootPath('docs/development/rate-limit-admin-login-report-only-policy.md'));
    $contents = (string) file_get_contents($rootPath('docs/development/rate-limit-admin-login-report-only-policy.md'));
    assertStringContainsString('admin.login', $contents);
    assertStringContainsString('report_only', $contents);
});

it('creates admin rate-limit contexts with opaque identity hashes', function (): void {
    $config = RateLimitRuntimeConfig::fromArray(['identity_salt' => 'salt']);
    $factory = new AdminRateLimitContextFactory(new RateLimitIdentityHasher(), $config);

    $context = $factory->create('admin.login', ['user@example.test', '127.0.0.1'], 123);

    assertSame('admin.login', $context->key);
    assertSame(123, $context->now);
    assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $context->identityHash);
});

it('provides admin login policy apply and audit tools', function () use ($rootPath): void {
    assertFileExists($rootPath('tools/apply-rate-limit-admin-login-policy.php'));
    assertFileExists($rootPath('tools/audit-rate-limit-admin-login-policy.php'));
});

it('runs the admin login policy audit', function () use ($rootPath): void {
    $outputDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'zoosper-admin-login-rate-' . bin2hex(random_bytes(6));
    $command = escapeshellarg(PHP_BINARY)
        . ' '
        . escapeshellarg($rootPath('tools/audit-rate-limit-admin-login-policy.php'))
        . ' --output-dir='
        . escapeshellarg($outputDir);
    exec($command, $output, $exitCode);
    assertSame(0, $exitCode);
    assertFileExists($outputDir . DIRECTORY_SEPARATOR . 'rate-limit-admin-login-policy.txt');
});
