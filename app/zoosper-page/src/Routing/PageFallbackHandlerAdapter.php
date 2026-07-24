<?php

declare(strict_types=1);

namespace Zoosper\Page\Routing;

use Zoosper\Core\Routing\FallbackHandlerInterface;

/**
 * Page-module adapter for the core fallback handler contract.
 *
 * This is intentionally a safe no-op proof adapter. A later phase can wire the
 * real page rendering fallback through this seam after runtime adapter tests are
 * added.
 */
final class PageFallbackHandlerAdapter implements FallbackHandlerInterface
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
