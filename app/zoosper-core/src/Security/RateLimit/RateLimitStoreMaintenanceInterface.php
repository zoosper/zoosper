<?php

declare(strict_types=1);

namespace Zoosper\Core\Security\RateLimit;

/** Operational maintenance boundary kept separate from request-time counters. */
interface RateLimitStoreMaintenanceInterface
{
    /** Delete fixed-window buckets whose end timestamp is at or before $now. */
    public function deleteExpired(int $now): int;
}
