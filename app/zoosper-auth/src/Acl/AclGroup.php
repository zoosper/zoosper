<?php

declare(strict_types=1);

namespace Zoosper\Auth\Acl;

final readonly class AclGroup
{
    /** @param list<array<string, mixed>> $permissions */
    public function __construct(
        public string $code,
        public string $label,
        public int $sortOrder,
        public array $permissions,
    ) {
    }
}
