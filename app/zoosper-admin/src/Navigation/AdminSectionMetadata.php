<?php

declare(strict_types=1);

namespace Zoosper\Admin\Navigation;

final readonly class AdminSectionMetadata
{
    public function __construct(
        public string $id,
        public string $label,
        public string $icon = '',
        public int $sortOrder = 100,
    ) {
    }
}
