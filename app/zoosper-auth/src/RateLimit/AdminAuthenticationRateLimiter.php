<?php

declare(strict_types=1);

namespace Zoosper\Auth\RateLimit;

use PDO;
use RuntimeException;
use Zoosper\Core\Security\RateLimit\AdminRateLimitContextFactory;
use Zoosper\Core\Security\RateLimit\DatabaseRateLimitStore;
use Zoosper\Core\Security\RateLimit\RateLimitDecision;
use Zoosper\Core\Security\RateLimit\RateLimitIdentityHasher;
use Zoosper\Core\Security\RateLimit\RateLimitRuntimeConfig;

final readonly class AdminAuthenticationRateLimiter implements AdminAuthenticationRateLimiterInterface
{
    public function __construct(private PDO $pdo, private string $basePath)
    {
    }

    public function checkPasswordLogin(string $email, ?string $clientIp): RateLimitDecision
    {
        $config = $this->config();
        $rule = $config->policies['admin.login'] ?? null;
        if (!$config->enabled || $rule === null) {
            return RateLimitDecision::allow(0, $rule?->maxAttempts ?? 1);
        }
        $this->assertSalt($config);
        $context = (new AdminRateLimitContextFactory(new RateLimitIdentityHasher(), $config))
            ->create('admin.login', [strtolower(trim($email)), $clientIp ?? '']);
        $store = new DatabaseRateLimitStore($this->pdo);
        $store->ensureSchema();
        $decision = $store->recordAttempt($rule, $context->identityHash, $context->now);
        return $config->isReportOnly()
            ? RateLimitDecision::allow($decision->attempts, $decision->maxAttempts)
            : $decision;
    }

    public function resetPasswordLogin(string $email, ?string $clientIp): void
    {
        $this->reset('admin.login', [strtolower(trim($email)), $clientIp ?? '']);
    }

    public function checkTwoFactor(int $adminUserId, ?string $clientIp): RateLimitDecision
    {
        $config = $this->config();
        $rule = $config->policies['admin.two_factor'] ?? null;
        if (!$config->enabled || $rule === null) {
            return RateLimitDecision::allow(0, $rule?->maxAttempts ?? 1);
        }
        $this->assertSalt($config);
        $context = (new AdminRateLimitContextFactory(new RateLimitIdentityHasher(), $config))
            ->create('admin.two_factor', [(string) $adminUserId, $clientIp ?? '']);
        $store = new DatabaseRateLimitStore($this->pdo);
        $store->ensureSchema();
        $decision = $store->recordAttempt($rule, $context->identityHash, $context->now);

        return $config->isReportOnly()
            ? RateLimitDecision::allow($decision->attempts, $decision->maxAttempts)
            : $decision;
    }

    public function resetTwoFactor(int $adminUserId, ?string $clientIp): void
    {
        $this->reset('admin.two_factor', [(string) $adminUserId, $clientIp ?? '']);
    }

    /** @param list<string> $parts */
    private function reset(string $key, array $parts): void
    {
        $config = $this->config();
        $rule = $config->policies[$key] ?? null;
        if (!$config->enabled || $rule === null) {
            return;
        }
        $this->assertSalt($config);
        $context = (new AdminRateLimitContextFactory(new RateLimitIdentityHasher(), $config))->create($key, $parts);
        $store = new DatabaseRateLimitStore($this->pdo);
        $store->ensureSchema();
        $store->reset($rule, $context->identityHash);
    }

    private function config(): RateLimitRuntimeConfig
    {
        $path = $this->basePath . '/app/zoosper-core/config/rate_limit.php';
        $config = is_file($path) ? require $path : [];
        return RateLimitRuntimeConfig::fromArray(is_array($config) ? $config : []);
    }

    private function assertSalt(RateLimitRuntimeConfig $config): void
    {
        if (trim($config->identitySalt) === '') {
            throw new RuntimeException('Enabled authentication rate limiting requires RATE_LIMIT_IDENTITY_SALT.');
        }
    }
}










