<?php

declare(strict_types=1);

namespace Zoosper\Auth\PasswordReset;

final readonly class AdminPasswordResetToken
{
    public function __construct(
        public int $id,
        public string $publicId,
        public int $adminUserId,
        public string $tokenHash,
        public string $expiresAt,
        public ?string $consumedAt,
        public string $createdAt,
    ) {
    }

    public function isConsumed(): bool
    {
        return $this->consumedAt !== null;
    }
}
