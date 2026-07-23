<?php

declare(strict_types=1);

use Zoosper\Core\Security\RateLimit\DatabaseRateLimitStore;
use Zoosper\Core\Security\RateLimit\RateLimitRule;

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

it('documents rate limit schema and cleanup tooling', function () use ($rootPath): void {
    assertFileExists($rootPath('docs/development/rate-limit-cleanup-and-schema.md'));
    $contents = (string) file_get_contents($rootPath('docs/development/rate-limit-cleanup-and-schema.md'));
    assertStringContainsString('rate_limit_buckets', $contents);
    assertStringContainsString('cleanup', $contents);
});

it('registers the rate limit bucket schema in core db schema config', function () use ($rootPath): void {
    assertFileExists($rootPath('app/zoosper-core/config/db_schema.php'));
    $schema = (string) file_get_contents($rootPath('app/zoosper-core/config/db_schema.php'));
    assertStringContainsString('rate_limit_buckets', $schema);
    assertStringContainsString('identity_hash', $schema);
    assertStringContainsString('window_ends_at', $schema);
});

it('provides a dry-run first expired bucket cleanup command', function () use ($rootPath): void {
    assertFileExists($rootPath('tools/cleanup-expired-rate-limit-buckets.php'));
    $source = (string) file_get_contents($rootPath('tools/cleanup-expired-rate-limit-buckets.php'));
    assertStringContainsString('--apply', $source);
    assertStringContainsString('--database=', $source);
    assertStringContainsString('deleteExpired', $source);
});

it('deletes expired buckets through the database store', function (): void {
    $pdo = new PDO('sqlite::memory:');
    $store = new DatabaseRateLimitStore($pdo);
    $store->ensureSchema();

    $rule = new RateLimitRule('admin.login', 2, 60, 'admin');
    $store->recordAttempt($rule, 'identity-hash', 120);
    $store->recordAttempt($rule, 'identity-hash', 180);

    assertSame(1, $store->deleteExpired(180));
    assertSame(1, $store->deleteExpired(240));
});

it('runs the cleanup readiness audit', function () use ($rootPath): void {
    assertFileExists($rootPath('tools/audit-rate-limit-cleanup-readiness.php'));
    $outputDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'zoosper-rate-cleanup-' . bin2hex(random_bytes(6));
    $command = escapeshellarg(PHP_BINARY)
        . ' '
        . escapeshellarg($rootPath('tools/audit-rate-limit-cleanup-readiness.php'))
        . ' --output-dir='
        . escapeshellarg($outputDir);
    exec($command, $output, $exitCode);
    assertSame(0, $exitCode);
    assertFileExists($outputDir . DIRECTORY_SEPARATOR . 'rate-limit-cleanup-readiness.txt');
    $log = (string) file_get_contents($outputDir . DIRECTORY_SEPARATOR . 'rate-limit-cleanup-readiness.log');
    assertStringContainsString('RATE_LIMIT_CLEANUP_READINESS_ERRORS 0', $log);
});
