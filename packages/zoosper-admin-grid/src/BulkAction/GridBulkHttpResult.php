<?php

declare(strict_types=1);

namespace Zoosper\AdminGrid\BulkAction;

/** Framework-neutral HTTP result produced after protected bulk execution. */
final readonly class GridBulkHttpResult
{
    public function __construct(
        public int $status,
        public string $message,
        public ?string $redirectPath = null,
    ) {
    }
}











