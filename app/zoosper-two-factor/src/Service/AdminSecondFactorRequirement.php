<?php

declare(strict_types=1);
namespace Zoosper\TwoFactor\Service;
use Zoosper\Auth\Contract\SecondFactorRequirementInterface;
final readonly class AdminSecondFactorRequirement implements SecondFactorRequirementInterface
{
    public function __construct(private AdminTwoFactorEnrollmentService $enrollment) {}
    public function requiresSecondFactor(int $adminUserId): bool { return !$this->enrollment->requiresEnrollment($adminUserId); }
}
