<?php

declare(strict_types=1);

namespace Zoosper\Core\Security\RateLimit;

/**
 * Receives report-only rate-limit decisions.
 */
interface RateLimitReportSinkInterface
{
    public function record(RateLimitReportEvent $event): void;
}
