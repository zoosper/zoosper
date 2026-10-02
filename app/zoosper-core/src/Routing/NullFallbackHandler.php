<?php

declare(strict_types=1);

namespace Zoosper\Core\Routing;

/**
 * Safe no-op fallback handler used when no feature module registers a fallback.
 */
final class NullFallbackHandler implements FallbackHandlerInterface
{
    #[\Override]
    public function supports(object $request): bool
    {
        return false;
    }

    #[\Override]
    public function handle(object $request): mixed
    {
        return null;
    }
}










