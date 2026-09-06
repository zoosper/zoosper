<?php

declare(strict_types=1);

namespace Zoosper\Auth\PasswordReset;

/** Delivers a one-time Admin reset credential without exposing transport ownership to Auth. */
interface AdminPasswordResetDeliveryInterface
{
    public function deliver(AdminPasswordResetIssue $issue, string $absoluteResetUrl): void;
}
