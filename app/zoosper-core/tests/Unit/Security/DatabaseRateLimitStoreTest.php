<?php

declare(strict_types=1);

use Zoosper\Core\Security\RateLimit\DatabaseRateLimitStore;
use Zoosper\Core\Security\RateLimit\RateLimitRule;

use function PHPUnit\Framework\assertFalse;
use function PHPUnit\Framework\assertFileExists;
use function PHPUnit\Framework\assertSame;
use function PHPUnit\Framework\assertStringContainsString;
use function PHPUnit\Framework\assertTrue;
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

it('documents the database rate limit store', function () use ($rootPath): void {
    assertFileExists($rootPath('docs/development/rate-limit-database-store.md'));
    assertFileExists($rootPath('database/schema/rate_limit_buckets.sql'));

    $contents = (string) file_get_contents($rootPath('docs/development/rate-limit-database-store.md'));
    assertStringContainsString('DatabaseRateLimitStore', $contents);
    assertStringContainsString('fixed-window', $contents);
});

it('allows attempts until the fixed window max is exceeded', function (): void {
    $pdo = new PDO('sqlite::memory:');
    $store = new DatabaseRateLimitStore($pdo);
    $store->ensureSchema();

    $rule = new RateLimitRule('admin.login', 2, 60, 'admin');

    $first = $store->recordAttempt($rule, 'identity-hash', 120);
    $second = $store->recordAttempt($rule, 'identity-hash', 121);
    $third = $store->recordAttempt($rule, 'identity-hash', 122);

    assertTrue($first->allowed);
    assertTrue($second->allowed);
    assertFalse($third->allowed);
    assertSame(3, $third->attempts);
    assertSame(58, $third->retryAfterSeconds);
});

it('starts a new bucket in the next fixed window', function (): void {
    $pdo = new PDO('sqlite::memory:');
    $store = new DatabaseRateLimitStore($pdo);
    $store->ensureSchema();

    $rule = new RateLimitRule('admin.login', 1, 60, 'admin');

    assertTrue($store->recordAttempt($rule, 'identity-hash', 120)->allowed);
    assertFalse($store->recordAttempt($rule, 'identity-hash', 121)->allowed);
    assertTrue($store->recordAttempt($rule, 'identity-hash', 180)->allowed);
});

it('can reset a rule identity bucket', function (): void {
    $pdo = new PDO('sqlite::memory:');
    $store = new DatabaseRateLimitStore($pdo);
    $store->ensureSchema();

    $rule = new RateLimitRule('admin.login', 1, 60, 'admin');

    assertTrue($store->recordAttempt($rule, 'identity-hash', 120)->allowed);
    assertFalse($store->recordAttempt($rule, 'identity-hash', 121)->allowed);

    $store->reset($rule, 'identity-hash');

    assertTrue($store->recordAttempt($rule, 'identity-hash', 122)->allowed);
});

it('runs the database rate limit store audit', function () use ($rootPath): void {
    assertFileExists($rootPath('tools/audit-rate-limit-database-store.php'));

    $outputDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'zoosper-rate-db-' . bin2hex(random_bytes(6));
    $command = escapeshellarg(PHP_BINARY)
        . ' '
        . escapeshellarg($rootPath('tools/audit-rate-limit-database-store.php'))
        . ' --output-dir='
        . escapeshellarg($outputDir);

    exec($command, $output, $exitCode);

    assertSame(0, $exitCode);
    assertFileExists($outputDir . DIRECTORY_SEPARATOR . 'rate-limit-database-store.txt');
    assertFileExists($outputDir . DIRECTORY_SEPARATOR . 'rate-limit-database-store.log');

    $log = (string) file_get_contents($outputDir . DIRECTORY_SEPARATOR . 'rate-limit-database-store.log');
    assertStringContainsString('RATE_LIMIT_DATABASE_STORE_ERRORS 0', $log);
});
