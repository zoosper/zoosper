<?php

declare(strict_types=1);

namespace Zoosper\AdminGrid;

/** Immutable outcome returned by feature-owned mutation controllers. */
final readonly class GridWorkspaceMutationResult
{
    public function __construct(
        public string $action,
        public string $message,
        public string $redirectPath,
    ) {
        if ($redirectPath === '' || $redirectPath[0] !== '/') {
            throw new \InvalidArgumentException(
                'Grid workspace redirects must use an absolute application path.',
            );
        }
    }
}
