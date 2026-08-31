<?php

declare(strict_types=1);

namespace Zoosper\Auth\RateLimit;

use Zoosper\Core\Security\RateLimit\RateLimitDecision;

interface AdminAuthenticationRateLimiterInterface
{
    public function checkPasswordLogin(string $email, ?string $clientIp): RateLimitDecision;
    public function resetPasswordLogin(string $email, ?string $clientIp): void;

    public function checkTwoFactor(int $adminUserId, ?string $clientIp): RateLimitDecision;

    public function resetTwoFactor(int $adminUserId, ?string $clientIp): void;
}










