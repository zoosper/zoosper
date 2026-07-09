<?php

declare(strict_types=1);

namespace Zoosper\Admin\Navigation;

final readonly class AdminMenuItem
{
    public function __construct(
        public string $code,
        public string $label,
        public string $url,
        public ?string $permission = null,
    ) {
    }
}
