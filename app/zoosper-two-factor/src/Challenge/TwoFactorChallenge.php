<?php

declare(strict_types=1);

namespace Zoosper\TwoFactor\Challenge;

/**
 * Immutable representation of a pending login-time 2FA challenge.
 *
 * A challenge is issued after a correct password but BEFORE the session is fully
 * authenticated. It is short-lived and single-use: the plaintext token is handed
 * to the client once (in the pending-2FA session), and only its SHA-256 hash is
 * persisted. The challenge is consumed when a valid TOTP code or recovery code is
 * supplied.
 */
final readonly class TwoFactorChallenge
{
    public function __construct(
        public int $id,
        public int $adminUserId,
        public string $tokenHash,
        public string $expiresAt,        // 'Y-m-d H:i:s' UTC
        public ?string $consumedAt,      // null while pending
        public string $createdAt,        // 'Y-m-d H:i:s' UTC
    ) {
    }

    /**
     * Is this challenge still usable at the given moment (default: now, UTC)?
     */
    public function isPending(?\DateTimeImmutable $now = null): bool
    {
        if ($this->consumedAt !== null) {
            return false;
        }

        $now ??= new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
        $expires = new \DateTimeImmutable($this->expiresAt, new \DateTimeZone('UTC'));

        return $now < $expires;
    }
}










