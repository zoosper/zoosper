<?php

declare(strict_types=1);

namespace Zoosper\Site\Model;

final readonly class SiteDomain
{
    public function __construct(
        public int $id,
        public int $siteId,
        public string $host,
        public bool $isPrimary,
    ) {
    }
}
