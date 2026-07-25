<?php

declare(strict_types=1);

namespace Zoosper\Core\Routing;

/**
 * Safe no-op fallback handler used when no feature module registers a fallback.
 */
final class NullFallbackHandler implements FallbackHandlerInterface
{
    public function supports(object $request): bool
    {
        return false;
    }

    public function handle(object $request): mixed
    {
        return null;
    }
}
