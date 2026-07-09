<?php

declare(strict_types=1);

namespace Zoosper\Site\Model;

final readonly class Site
{
    public function __construct(
        public int $id,
        public string $code,
        public string $name,
        public string $status,
        public ?string $homepageSlug = null,
        public string $themeCode = 'default',
    ) {
    }
}
