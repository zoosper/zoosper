<?php

declare(strict_types=1);

namespace Zoosper\Auth\PasswordReset;

use Zoosper\Auth\AccountLockout\AdminAccountLockoutService;

use Closure;
use PDO;
use RuntimeException;
use Throwable;
use Zoosper\Auth\Repository\AdminUserRepository;
use Zoosper\Auth\Security\PasswordPolicy;
use Zoosper\Auth\Service\PasswordHasher;

/** Issues and consumes Admin password-reset credentials without persisting plaintext secrets. */
final readonly class AdminPasswordResetService
{
    private const TOKEN_PATTERN = '/^zp_reset_([a-f0-9]{16})_([a-f0-9]{64})$/D';

    public function __construct(
        private PDO $pdo,
        private AdminUserRepository $users,
        private AdminPasswordResetTokenRepository $tokens,
        private PasswordHasher $hasher,
        private PasswordPolicy $policy,
        private int $lifetimeSeconds = 3600,
        private ?Closure $clock = null,
        private ?AdminAccountLockoutService $lockouts = null,
    ) {
    }

    public function issueForEmail(string $email): ?AdminPasswordResetIssue
    {
        $user = $this->users->findByEmail(mb_strtolower(trim($email)));
        if ($user === null || !$user->isActive()) {
            return null;
        }

        $now = $this->now();
        $publicId = bin2hex(random_bytes(8));
        $secret = bin2hex(random_bytes(32));
        $plaintext = 'zp_reset_' . $publicId . '_' . $secret;
        $createdAt = gmdate('Y-m-d H:i:s', $now);
        $expiresAt = gmdate('Y-m-d H:i:s', $now + max(300, $this->lifetimeSeconds));

        $this->pdo->beginTransaction();
        try {
            $this->tokens->invalidateOutstandingForUser($user->id, $createdAt);
            $this->tokens->create($user->id, $publicId, hash('sha256', $plaintext), $expiresAt, $createdAt);
            $this->pdo->commit();
        } catch (Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $exception;
        }

        return new AdminPasswordResetIssue($user->id, $user->email, $plaintext, $expiresAt);
    }

    /** @return list<string> */
    public function reset(string $plaintextToken, string $password, string $confirmation): array
    {
        if ($password !== $confirmation) {
            return ['Password confirmation does not match.'];
        }
        $violations = $this->policy->violations($password);
        if ($violations !== []) {
            return $violations;
        }
        if (preg_match(self::TOKEN_PATTERN, $plaintextToken, $match) !== 1) {
            return ['This password reset link is invalid or has expired.'];
        }
        $token = $this->tokens->findByPublicId($match[1]);
        $now = $this->now();
        if ($token === null
            || $token->isConsumed()
            || strtotime($token->expiresAt . ' UTC') < $now
            || !hash_equals($token->tokenHash, hash('sha256', $plaintextToken))) {
            return ['This password reset link is invalid or has expired.'];
        }
        $user = $this->users->findById($token->adminUserId);
        if ($user === null || !$user->isActive()) {
            return ['This password reset link is invalid or has expired.'];
        }

        $consumedAt = gmdate('Y-m-d H:i:s', $now);
        $this->pdo->beginTransaction();
        try {
            if (!$this->tokens->consume($token->id, $consumedAt)) {
                throw new RuntimeException('Password reset token was already consumed.');
            }
            $this->users->updatePassword($user->id, $this->hasher->hash($password));
            $this->tokens->invalidateOutstandingForUser($user->id, $consumedAt);
            $this->lockouts?->clear($user->id);
            $this->pdo->commit();
        } catch (Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            return ['This password reset link is invalid or has expired.'];
        }

        return [];
    }

    private function now(): int
    {
        return $this->clock !== null ? (int) ($this->clock)() : time();
    }
}
