<?php

declare(strict_types=1);

namespace Zoosper\Auth\Model;

final readonly class AdminUser
{
    /**
     * @param list<string> $permissions
     */
    public function __construct(
        public int $id,
        public string $email,
        public string $name,
        public string $passwordHash,
        public string $status,
        public array $permissions,
        public ?string $locale = null,
    ) {
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function can(string $permission): bool
    {
        return in_array($permission, $this->permissions, true);
    }
}
