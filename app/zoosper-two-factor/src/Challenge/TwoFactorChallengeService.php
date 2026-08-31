<?php

declare(strict_types=1);

namespace Zoosper\TwoFactor\Challenge;

use Closure;

/**
 * Issues and resolves login-time 2FA challenges.
 *
 * Flow:
 *   1. After a correct password, the login controller calls issue($adminUserId)
 *      and stores the returned plaintext token in the pending-2FA session.
 *   2. The challenge page collects a 6-digit TOTP code (or a recovery code) and
 *      posts it with the token.
 *   3. verifyTotp()/verifyRecoveryCode() validate the code, atomically consume
 *      the challenge, and return a result used to promote the session to
 *      fully-authenticated.
 *
 * Only the SHA-256 hash of the token is persisted. Codes are checked via injected
 * Closures so this service has no hard dependency on the concrete TOTP verifier
 * or recovery-code repository (keeps it unit-testable; live wiring passes real
 * closures that call Totp\TotpVerifier and the recovery-code repo).
 *
 * Note: PHP readonly properties must be typed, and `callable` is not a valid
 * property type, so the injected callables are stored as typed Closure props
 * (converted via Closure::fromCallable so array/callable forms are accepted too).
 */
final readonly class TwoFactorChallengeService
{
    private Closure $verifyTotp;
    private Closure $redeemRecoveryCode;

    /**
     * @param callable(string $secret, string $code): bool   $verifyTotp
     * @param callable(int $adminUserId, string $code): bool $redeemRecoveryCode
     */
    public function __construct(
        private TwoFactorChallengeRepository $challenges,
        callable $verifyTotp,
        callable $redeemRecoveryCode,
        private int $ttlSeconds = 300,
        private ?\DateTimeImmutable $now = null,
    ) {
        $this->verifyTotp = Closure::fromCallable($verifyTotp);
        $this->redeemRecoveryCode = Closure::fromCallable($redeemRecoveryCode);
    }

    /**
     * Issue a new challenge for a user, returning the PLAINTEXT token to store in
     * the pending-2FA session. Existing pending challenges for the user are
     * cleared first so only one is live at a time.
     */
    public function issue(int $adminUserId): string
    {
        $this->challenges->deleteForUser($adminUserId);

        $token = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $token);

        $now = $this->now();
        $expires = $now->add(new \DateInterval('PT' . max(1, $this->ttlSeconds) . 'S'));

        $this->challenges->insert(
            adminUserId: $adminUserId,
            tokenHash: $tokenHash,
            expiresAt: $expires->format('Y-m-d H:i:s'),
            createdAt: $now->format('Y-m-d H:i:s'),
        );

        return $token;
    }

    /**
     * Verify a submitted TOTP code against a pending challenge and consume it.
     */
    public function verifyTotp(string $token, string $code, string $secret): TwoFactorChallengeResult
    {
        $challenge = $this->pendingChallengeFor($token);
        if ($challenge === null) {
            return TwoFactorChallengeResult::invalidOrExpired();
        }

        if (!($this->verifyTotp)($secret, $code)) {
            return TwoFactorChallengeResult::wrongCode($challenge->adminUserId);
        }

        return $this->consume($challenge);
    }

    /**
     * Verify a recovery code against a pending challenge and consume it. The
     * recovery-code redemption callable is responsible for marking the code used.
     */
    public function verifyRecoveryCode(string $token, string $code): TwoFactorChallengeResult
    {
        $challenge = $this->pendingChallengeFor($token);
        if ($challenge === null) {
            return TwoFactorChallengeResult::invalidOrExpired();
        }

        if (!($this->redeemRecoveryCode)($challenge->adminUserId, $code)) {
            return TwoFactorChallengeResult::wrongCode($challenge->adminUserId);
        }

        return $this->consume($challenge);
    }

    /**
     * Resolve the pending challenge for a plaintext token, or null when the token
     * is unknown, already consumed, or expired.
     */
    private function pendingChallengeFor(string $token): ?TwoFactorChallenge
    {
        $token = trim($token);
        if ($token === '') {
            return null;
        }

        $challenge = $this->challenges->findByTokenHash(hash('sha256', $token));
        if ($challenge === null || !$challenge->isPending($this->now())) {
            return null;
        }

        return $challenge;
    }

    private function consume(TwoFactorChallenge $challenge): TwoFactorChallengeResult
    {
        // Atomic single-use: only the first caller to flip consumed_at wins.
        $consumed = $this->challenges->markConsumed(
            $challenge->id,
            $this->now()->format('Y-m-d H:i:s'),
        );

        if (!$consumed) {
            return TwoFactorChallengeResult::invalidOrExpired();
        }

        return TwoFactorChallengeResult::success($challenge->adminUserId);
    }

    private function now(): \DateTimeImmutable
    {
        return $this->now ?? new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
    }
}










