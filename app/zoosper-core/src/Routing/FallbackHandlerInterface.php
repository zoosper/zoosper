<?php

declare(strict_types=1);

namespace Zoosper\Core\Routing;

/**
 * Core-owned contract for route fallback handling.
 *
 * Feature modules, such as the page module, can later provide concrete fallback
 * handlers without zoosper-core importing their controllers directly.
 */
interface FallbackHandlerInterface
{
    /**
     * Return true when this handler can handle the current unmatched request.
     */
    public function supports(object $request): bool;

    /**
     * Handle the unmatched request and return a framework response object/value.
     */
    public function handle(object $request): mixed;
}
