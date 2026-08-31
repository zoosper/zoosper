<?php

declare(strict_types=1);
namespace Zoosper\Auth\Contract;
interface SecondFactorRequirementInterface
{
    public function requiresSecondFactor(int $adminUserId): bool;
}










