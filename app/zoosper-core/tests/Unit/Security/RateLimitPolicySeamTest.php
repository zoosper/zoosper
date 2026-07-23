<?php

declare(strict_types=1);

use Zoosper\Core\Security\RateLimit\RateLimitDecision;
use Zoosper\Core\Security\RateLimit\RateLimitEnforcer;
use Zoosper\Core\Security\RateLimit\RateLimitPolicy;
use Zoosper\Core\Security\RateLimit\RateLimitRule;
use Zoosper\Core\Security\RateLimit\RateLimitStoreInterface;
use Zoosper\Core\Security\RateLimit\StaticRateLimitPolicyResolver;

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

it('documents the rate limit policy seam', function () use ($rootPath): void {
    assertFileExists($rootPath('docs/development/rate-limit-policy-and-enforcement-seam.md'));
    $contents = (string) file_get_contents($rootPath('docs/development/rate-limit-policy-and-enforcement-seam.md'));
    assertStringContainsString('RateLimitPolicy', $contents);
    assertStringContainsString('RateLimitEnforcer', $contents);
    assertStringContainsString('middleware', $contents);
});

it('resolves static policies by key', function (): void {
    $rule = new RateLimitRule('admin.login', 5, 60, 'admin');
    $resolver = new StaticRateLimitPolicyResolver(['admin.login' => $rule]);

    $enabled = $resolver->resolve('admin.login');
    $disabled = $resolver->resolve('public.page');

    assertTrue($enabled->enabled);
    assertSame($rule, $enabled->rule);
    assertFalse($disabled->enabled);
});

it('does not touch the store for disabled policies', function (): void {
    $store = new class implements RateLimitStoreInterface {
        public int $calls = 0;
        public function recordAttempt(RateLimitRule $rule, string $identityHash, int $now): RateLimitDecision
        {
            $this->calls++;
            return RateLimitDecision::deny(99, 1, 60);
        }
        public function reset(RateLimitRule $rule, string $identityHash): void
        {
        }
    };

    $decision = (new RateLimitEnforcer($store))->check(RateLimitPolicy::disabled(), 'identity-hash', 100);

    assertTrue($decision->allowed);
    assertSame(0, $store->calls);
});

it('uses the store for enabled policies', function (): void {
    $rule = new RateLimitRule('admin.login', 1, 60, 'admin');
    $store = new class implements RateLimitStoreInterface {
        public int $calls = 0;
        public function recordAttempt(RateLimitRule $rule, string $identityHash, int $now): RateLimitDecision
        {
            $this->calls++;
            return RateLimitDecision::allow(1, $rule->maxAttempts);
        }
        public function reset(RateLimitRule $rule, string $identityHash): void
        {
        }
    };

    $decision = (new RateLimitEnforcer($store))->check(RateLimitPolicy::enabled($rule), 'identity-hash', 100);

    assertTrue($decision->allowed);
    assertSame(1, $store->calls);
});

it('runs the rate limit policy seam audit', function () use ($rootPath): void {
    assertFileExists($rootPath('tools/audit-rate-limit-policy-seam.php'));
    $outputDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'zoosper-rate-policy-' . bin2hex(random_bytes(6));
    $command = escapeshellarg(PHP_BINARY)
        . ' '
        . escapeshellarg($rootPath('tools/audit-rate-limit-policy-seam.php'))
        . ' --output-dir='
        . escapeshellarg($outputDir);
    exec($command, $output, $exitCode);
    assertSame(0, $exitCode);
    assertFileExists($outputDir . DIRECTORY_SEPARATOR . 'rate-limit-policy-seam.txt');
    $log = (string) file_get_contents($outputDir . DIRECTORY_SEPARATOR . 'rate-limit-policy-seam.log');
    assertStringContainsString('RATE_LIMIT_POLICY_SEAM_ERRORS 0', $log);
});
