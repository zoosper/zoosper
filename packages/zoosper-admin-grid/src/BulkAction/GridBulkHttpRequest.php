<?php

declare(strict_types=1);

namespace Zoosper\AdminGrid\BulkAction;

/** Minimal framework-neutral HTTP input for a protected Admin Grid bulk action. */
final readonly class GridBulkHttpRequest
{
    /** @param array<string, mixed> $form */
    public function __construct(
        public string $method,
        public array $form,
    ) {
    }
}
