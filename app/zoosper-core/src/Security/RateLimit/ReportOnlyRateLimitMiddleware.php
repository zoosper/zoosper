<?php

declare(strict_types=1);

namespace Zoosper\Core\Security\RateLimit;

use Closure;

/**
 * Non-enforcing adapter for reporting rate-limit decisions before enforcement is enabled.
 */
final class ReportOnlyRateLimitMiddleware
{
    public function __construct(
        private RateLimitGuard $guard,
        private RateLimitReportSinkInterface $reports,
    ) {
    }

    /**
     * Executes the downstream handler regardless of the rate-limit decision.
     *
     * The downstream callable may accept the decision as its first argument for
     * diagnostics, but it is not required to do so.
     *
     * @template T
     * @param callable(RateLimitDecision=):T $next
     * @return T
     */
    public function handle(RateLimitContext $context, callable $next): mixed
    {
        $decision = $this->guard->check($context);
        $this->reports->record(RateLimitReportEvent::fromDecision($context, $decision));

        return $next($decision);
    }
}
