<?php

declare(strict_types=1);

use Zoosper\Core\Security\RateLimit\RateLimitContext;
use Zoosper\Core\Security\RateLimit\RateLimitDecision;
use Zoosper\Core\Security\RateLimit\RateLimitEnforcer;
use Zoosper\Core\Security\RateLimit\RateLimitGuard;
use Zoosper\Core\Security\RateLimit\RateLimitIdentityHasher;
use Zoosper\Core\Security\RateLimit\RateLimitRule;
use Zoosper\Core\Security\RateLimit\RateLimitStoreInterface;
use Zoosper\Core\Security\RateLimit\StaticRateLimitPolicyResolver;

use function PHPUnit\Framework\assertFileExists;
use function PHPUnit\Framework\assertFalse;
use function PHPUnit\Framework\assertMatchesRegularExpression;
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

it('documents the rate limit guard and identity seam', function () use ($rootPath): void {
    assertFileExists($rootPath('docs/development/rate-limit-guard-and-identity.md'));
    $contents = (string) file_get_contents($rootPath('docs/development/rate-limit-guard-and-identity.md'));
    assertStringContainsString('RateLimitContext', $contents);
    assertStringContainsString('RateLimitIdentityHasher', $contents);
    assertStringContainsString('RateLimitGuard', $contents);
});

it('hashes identity parts into an opaque sha256 value', function (): void {
    $hash = (new RateLimitIdentityHasher())->hash([' admin ', '127.0.0.1'], 'salt');
    assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $hash);
    assertSame($hash, (new RateLimitIdentityHasher())->hash(['admin', '127.0.0.1'], 'salt'));
});

it('uses policy resolver and enforcer through the guard', function (): void {
    $rule = new RateLimitRule('admin.login', 1, 60, 'admin');
    $store = new class implements RateLimitStoreInterface {
        public int $calls = 0;
        public function recordAttempt(RateLimitRule $rule, string $identityHash, int $now): RateLimitDecision
        {
            $this->calls++;
            return $this->calls === 1
                ? RateLimitDecision::allow(1, $rule->maxAttempts)
                : RateLimitDecision::deny(2, $rule->maxAttempts, 30);
        }
        public function reset(RateLimitRule $rule, string $identityHash): void
        {
        }
    };

    $guard = new RateLimitGuard(
        new StaticRateLimitPolicyResolver(['admin.login' => $rule]),
        new RateLimitEnforcer($store),
    );

    $context = new RateLimitContext('admin.login', 'identity-hash', 100);

    assertTrue($guard->check($context)->allowed);
    assertFalse($guard->check($context)->allowed);
    assertSame(2, $store->calls);
});

it('runs the rate limit guard seam audit', function () use ($rootPath): void {
    assertFileExists($rootPath('tools/audit-rate-limit-guard-seam.php'));
    $outputDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'zoosper-rate-guard-' . bin2hex(random_bytes(6));
    $command = escapeshellarg(PHP_BINARY)
        . ' '
        . escapeshellarg($rootPath('tools/audit-rate-limit-guard-seam.php'))
        . ' --output-dir='
        . escapeshellarg($outputDir);
    exec($command, $output, $exitCode);
    assertSame(0, $exitCode);
    assertFileExists($outputDir . DIRECTORY_SEPARATOR . 'rate-limit-guard-seam.txt');
    $log = (string) file_get_contents($outputDir . DIRECTORY_SEPARATOR . 'rate-limit-guard-seam.log');
    assertStringContainsString('RATE_LIMIT_GUARD_SEAM_ERRORS 0', $log);
});
