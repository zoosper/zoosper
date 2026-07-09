<?php

declare(strict_types=1);

namespace Zoosper\Auth\Access;

final readonly class Role
{
    /** @param list<Permission> $permissions */
    public function __construct(
        public string $code,
        public string $label,
        public array $permissions,
    ) {
    }

    public function allows(Permission $permission): bool
    {
        return in_array($permission, $this->permissions, true);
    }
}
