<?php

declare(strict_types=1);

namespace Zoosper\Core\Security\RateLimit;

/**
 * In-memory report sink for tests and diagnostics.
 */
final class InMemoryRateLimitReportSink implements RateLimitReportSinkInterface
{
    /** @var list<RateLimitReportEvent> */
    private array $events = [];

    public function record(RateLimitReportEvent $event): void
    {
        $this->events[] = $event;
    }

    /** @return list<RateLimitReportEvent> */
    public function events(): array
    {
        return $this->events;
    }
}
