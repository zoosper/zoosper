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
 * Password login and two-factor checks evaluate independent subject, client-IP,
 * and subject-plus-IP dimensions. Any enforcing dimension may deny the request.
 * Successful authentication clears only subject and paired buckets so one user
 * can never erase the shared abuse history accumulated by an IP address.
 */
final readonly class AdminAuthenticationRateLimiter implements AdminAuthenticationRateLimiterInterface
{
    public function __construct(private PDO $pdo, private string $basePath)
    {
    }

    #[\Override]
    public function checkPasswordLogin(string $email, ?string $clientIp): RateLimitDecision
    {
        $subject = strtolower(trim($email));

        return $this->checkDimensions([
            ['admin.login.subject', [$subject]],
            ['admin.login.pair', [$subject, $this->normalisedIp($clientIp)]],
            ...$this->ipDimension('admin.login.ip', $clientIp),
        ]);
    }

    #[\Override]
    public function resetPasswordLogin(string $email, ?string $clientIp): void
    {
        $subject = strtolower(trim($email));
        $this->reset('admin.login.subject', [$subject]);
        $this->reset('admin.login.pair', [$subject, $this->normalisedIp($clientIp)]);
    }

    #[\Override]
    public function checkPasswordResetRequest(string $email, ?string $clientIp): RateLimitDecision
    {
        return $this->check('admin.password_reset_request', [
            strtolower(trim($email)),
            $clientIp ?? '',
        ]);
    }

    #[\Override]
    public function checkTwoFactor(int $adminUserId, ?string $clientIp): RateLimitDecision
    {
        $subject = (string) $adminUserId;

        return $this->checkDimensions([
            ['admin.two_factor.subject', [$subject]],
            ['admin.two_factor.pair', [$subject, $this->normalisedIp($clientIp)]],
            ...$this->ipDimension('admin.two_factor.ip', $clientIp),
        ]);
    }

    #[\Override]
    public function resetTwoFactor(int $adminUserId, ?string $clientIp): void
    {
        $subject = (string) $adminUserId;
        $this->reset('admin.two_factor.subject', [$subject]);
        $this->reset('admin.two_factor.pair', [$subject, $this->normalisedIp($clientIp)]);
    }

    /**
     * @param list<array{0: string, 1: list<string>}> $dimensions
     */
    private function checkDimensions(array $dimensions): RateLimitDecision
    {
        $decisions = [];
        foreach ($dimensions as [$key, $identityParts]) {
            $decisions[] = $this->check($key, $identityParts);
        }

        $denied = array_values(array_filter(
            $decisions,
            static fn (RateLimitDecision $decision): bool => !$decision->allowed,
        ));
        if ($denied !== []) {
            usort($denied, static fn (RateLimitDecision $left, RateLimitDecision $right): int =>
                $right->retryAfterSeconds <=> $left->retryAfterSeconds);

            return $denied[0];
        }

        $representative = $decisions[0] ?? RateLimitDecision::allow(0, 1);

        return RateLimitDecision::allow($representative->attempts, $representative->maxAttempts);
    }

    /** @return list<array{0: string, 1: list<string>}> */
    private function ipDimension(string $key, ?string $clientIp): array
    {
        $ip = $this->normalisedIp($clientIp);

        return $ip === '' ? [] : [[$key, [$ip]]];
    }

    private function normalisedIp(?string $clientIp): string
    {
        return trim($clientIp ?? '');
    }

    /** @param list<string> $identityParts */
    private function check(string $key, array $identityParts): RateLimitDecision
    {
        $config = $this->config();
        $rule = $config->policies[$key] ?? null;
        if (!$config->enabled || $rule === null) {
            return RateLimitDecision::allow(0, $rule?->maxAttempts ?? 1);
        }

        $this->assertSalt($config);
        $context = $this->context($config, $key, $identityParts);
        $decision = $this->store()->recordAttempt($rule, $context->identityHash, $context->now);
        if ($config->isReportOnly()) {
            $this->report($config, $context, $decision);

            return RateLimitDecision::allow($decision->attempts, $decision->maxAttempts);
        }

        return $decision;
    }

    /** @param list<string> $identityParts */
    private function reset(string $key, array $identityParts): void
    {
        $config = $this->config();
        $rule = $config->policies[$key] ?? null;
        if (!$config->enabled || $rule === null) {
            return;
        }

        $this->assertSalt($config);
        $context = $this->context($config, $key, $identityParts);
        $this->store()->reset($rule, $context->identityHash);
    }

    /** @param list<string> $identityParts */
    private function context(RateLimitRuntimeConfig $config, string $key, array $identityParts): RateLimitContext
    {
        return (new AdminRateLimitContextFactory(new RateLimitIdentityHasher(), $config))->create($key, $identityParts);
    }

    private function store(): RateLimitStoreInterface
    {
        $store = new DatabaseRateLimitStore($this->pdo);
        $store->ensureSchema();

        return $store;
    }

    private function report(RateLimitRuntimeConfig $config, RateLimitContext $context, RateLimitDecision $decision): void
    {
        $path = $this->basePath . '/' . ltrim($config->reportPath, '/');
        (new FileRateLimitReportSink($path))->record(RateLimitReportEvent::fromDecision($context, $decision));
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
