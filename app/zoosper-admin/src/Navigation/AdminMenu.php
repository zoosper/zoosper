<?php

declare(strict_types=1);

namespace Zoosper\Admin\Navigation;

use Zoosper\Auth\Access\Permission;
use Zoosper\Auth\Model\AdminUser;

final readonly class AdminMenu
{
    /** @return list<AdminMenuItem> */
    public function itemsFor(AdminUser $user): array
    {
        $items = [
            new AdminMenuItem('dashboard', 'Dashboard', '/admin', Permission::AdminAccess->value),
            new AdminMenuItem('pages', 'Pages', '/admin/pages', Permission::PageManage->value),
            new AdminMenuItem('sites', 'Sites', '#', Permission::SettingsManage->value),
            new AdminMenuItem('settings', 'Settings', '#', Permission::SettingsManage->value),
        ];

        return array_values(array_filter(
            $items,
            static fn (AdminMenuItem $item): bool => $item->permission === null || $user->can($item->permission),
        ));
    }
}
