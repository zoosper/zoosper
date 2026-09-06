<?php

declare(strict_types=1);

namespace Zoosper\Auth\PasswordReset;

/** Plaintext token exists only at the issue boundary and is never persisted. */
final readonly class AdminPasswordResetIssue
{
    public function __construct(
        public int $adminUserId,
        public string $email,
        public string $token,
        public string $expiresAt,
    ) {
    }
}
