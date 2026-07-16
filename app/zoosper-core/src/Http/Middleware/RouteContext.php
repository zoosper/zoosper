<?php

declare(strict_types=1);

namespace Zoosper\Core\Http\Middleware;

/**
 * Immutable route metadata passed to middleware.
 *
 * Phase 1.33: surfaces the dormant per-route `public` and `permission` flags
 * (already declared in module route configs) so middleware can enforce the
 * designed access model without controllers re-checking by hand.
 */
final readonly class RouteContext
{
    public function __construct(
        public string $method,
        public string $path,
        public bool $isPublic = false,
        public ?string $permission = null,
    ) {
    }
}