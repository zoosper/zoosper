<?php

declare(strict_types=1);

namespace Zoosper\Core\Routing;

/**
 * Handles final fallback routing without importing feature-module controllers.
 */
interface FallbackHandlerInterface
{
    public function supports(object $request): bool;

    public function handle(object $request): mixed;
}










