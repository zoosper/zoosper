<?php

declare(strict_types=1);

namespace Zoosper\Admin\Navigation;

use Zoosper\Auth\Model\AdminUser;

final readonly class AdminMenu
{
    public function __construct(private AdminMenuLoader $loader)
    {
    }

    /**
     * @return list<AdminMenuItem>
     */
    public function itemsFor(AdminUser $user): array
    {
        return array_values(array_filter(
            $this->loader->load(),
            static fn (AdminMenuItem $item): bool => $item->isAllowed(
                static fn (string $permission): bool => $user->can($permission),
            ),
        ));
    }
}
