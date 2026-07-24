<?php

declare(strict_types=1);

namespace Zoosper\Core\Routing;

/**
 * Safe default fallback handler used before feature modules register one.
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
