<?php

declare(strict_types=1);

namespace Zoosper\AdminGrid;

/** Value object supplied by the host application's existing CSRF service. */
final readonly class GridWorkspaceCsrf
{
    public function __construct(
        public string $field,
        public string $token,
    ) {
        if (trim($field) === '' || trim($token) === '') {
            throw new \InvalidArgumentException(
                'Grid workspace CSRF field and token must be non-empty.',
            );
        }
    }
}











