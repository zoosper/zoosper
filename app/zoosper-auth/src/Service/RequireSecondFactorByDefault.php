<?php

declare(strict_types=1);
namespace Zoosper\Auth\Service;
use Zoosper\Auth\Contract\SecondFactorRequirementInterface;
final readonly class RequireSecondFactorByDefault implements SecondFactorRequirementInterface
{
    #[\Override]
    public function requiresSecondFactor(int $adminUserId): bool { return true; }
}










