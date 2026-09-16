<?php

declare(strict_types=1);

namespace Zoosper\Auth\RateLimit;

use PDO;
use RuntimeException;
use Zoosper\Core\Security\RateLimit\AdminRateLimitContextFactory;
use Zoosper\Core\Security\RateLimit\DatabaseRateLimitStore;
use Zoosper\Core\Security\RateLimit\FileRateLimitReportSink;
use Zoosper\Core\Security\RateLimit\RateLimitContext;
use Zoosper\Core\Security\RateLimit\RateLimitDecision;
use Zoosper\Core\Security\RateLimit\RateLimitIdentityHasher;
use Zoosper\Core\Security\RateLimit\RateLimitReportEvent;
use Zoosper\Core\Security\RateLimit\RateLimitRuntimeConfig;
use Zoosper\Core\Security\RateLimit\RateLimitStoreInterface;

/**
 * Canonical policy-execution boundary for public Admin authentication controls.
 *
 * HTTP adapters select an authentication operation and retain responsibility
 * for their HTML, JSON or neutral response shape. This service alone owns
 * runtime configuration, opaque identity construction, persistence,
 * report-only diagnostics, enforcement decisions and successful resets.
 */
final readonly class AdminAuthenticationRateLimiter implements AdminAuthenticationRateLimiterInterface
{
    public function __construct(
        private PDO $pdo,
        private string $basePath,
    ) {
    }

    public function checkPasswordLogin(
        string $email,
        ?string $clientIp,
    ): RateLimitDecision {
        return $this->check('admin.login', [
            strtolower(trim($email)),
            $clientIp ?? '',
        ]);
    }

    public function resetPasswordLogin(
        string $email,
        ?string $clientIp,
    ): void {
        $this->reset('admin.login', [
            strtolower(trim($email)),
            $clientIp ?? '',
        ]);
    }

    public function checkPasswordResetRequest(
        string $email,
        ?string $clientIp,
    ): RateLimitDecision {
        return $this->check('admin.password_reset_request', [
            strtolower(trim($email)),
            $clientIp ?? '',
        ]);
    }

    public function checkTwoFactor(
        int $adminUserId,
        ?string $clientIp,
    ): RateLimitDecision {
        return $this->check('admin.two_factor', [
            (string) $adminUserId,
            $clientIp ?? '',
        ]);
    }

    public function resetTwoFactor(
        int $adminUserId,
        ?string $clientIp,
    ): void {
        $this->reset('admin.two_factor', [
            (string) $adminUserId,
            $clientIp ?? '',
        ]);
    }

    /**
     * @param list<string> $identityParts
     */
    private function check(
        string $key,
        array $identityParts,
    ): RateLimitDecision {
        $config = $this->config();
        $rule = $config->policies[$key] ?? null;

        if (!$config->enabled || $rule === null) {
            return RateLimitDecision::allow(
                0,
                $rule?->maxAttempts ?? 1,
            );
        }

        $this->assertSalt($config);

        $context = $this->context(
            $config,
            $key,
            $identityParts,
        );
        $decision = $this->store()->recordAttempt(
            $rule,
            $context->identityHash,
            $context->now,
        );

        if ($config->isReportOnly()) {
            $this->report($config, $context, $decision);

            return RateLimitDecision::allow(
                $decision->attempts,
                $decision->maxAttempts,
            );
        }

        return $decision;
    }

    /**
     * @param list<string> $identityParts
     */
    private function reset(string $key, array $identityParts): void
    {
        $config = $this->config();
        $rule = $config->policies[$key] ?? null;

        if (!$config->enabled || $rule === null) {
            return;
        }

        $this->assertSalt($config);

        $context = $this->context(
            $config,
            $key,
            $identityParts,
        );
        $this->store()->reset($rule, $context->identityHash);
    }

    /**
     * @param list<string> $identityParts
     */
    private function context(
        RateLimitRuntimeConfig $config,
        string $key,
        array $identityParts,
    ): RateLimitContext {
        return (new AdminRateLimitContextFactory(
            new RateLimitIdentityHasher(),
            $config,
        ))->create($key, $identityParts);
    }

    private function store(): RateLimitStoreInterface
    {
        $store = new DatabaseRateLimitStore($this->pdo);
        $store->ensureSchema();

        return $store;
    }

    private function report(
        RateLimitRuntimeConfig $config,
        RateLimitContext $context,
        RateLimitDecision $decision,
    ): void {
        $path = $this->basePath
            . '/'
            . ltrim($config->reportPath, '/');

        (new FileRateLimitReportSink($path))->record(
            RateLimitReportEvent::fromDecision(
                $context,
                $decision,
            ),
        );
    }

    private function config(): RateLimitRuntimeConfig
    {
        $path = $this->basePath
            . '/app/zoosper-core/config/rate_limit.php';
        $config = is_file($path) ? require $path : [];

        return RateLimitRuntimeConfig::fromArray(
            is_array($config) ? $config : [],
        );
    }

    private function assertSalt(
        RateLimitRuntimeConfig $config,
    ): void {
        if (trim($config->identitySalt) === '') {
            throw new RuntimeException(
                'Enabled authentication rate limiting requires '
                . 'RATE_LIMIT_IDENTITY_SALT.'
            );
        }
    }
}