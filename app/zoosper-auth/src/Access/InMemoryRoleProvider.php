<?php

declare(strict_types=1);

namespace Zoosper\Auth\Access;

final readonly class InMemoryRoleProvider implements RoleProviderInterface
{
    /** @param array<string, Role> $roles */
    private function __construct(private array $roles)
    {
    }

    public static function createDefault(): self
    {
        return new self([
            'super_admin' => new Role(
                code: 'super_admin',
                label: 'Super Admin',
                permissions: Permission::cases(),
            ),
            'content_admin' => new Role(
                code: 'content_admin',
                label: 'Content Admin',
                permissions: [Permission::AdminAccess, Permission::PageView, Permission::PageManage],
            ),
            'api_consumer' => new Role(
                code: 'api_consumer',
                label: 'API Consumer',
                permissions: [Permission::ApiAccess],
            ),
        ]);
    }

    public function get(string $code): ?Role
    {
        return $this->roles[$code] ?? null;
    }
}
