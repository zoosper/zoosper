<?php

declare(strict_types=1);

namespace Zoosper\Admin\Navigation;

use Marko\Admin\Contracts\AdminSectionInterface;
use Marko\Admin\Contracts\MenuItemInterface;
use Zoosper\Auth\Model\AdminUser;

final readonly class AdminMenu
{
    public function __construct(
        private AdminMenuLoader $loader,
        private AdminSectionBuilder $sections = new AdminSectionBuilder(),
    )
    {
    }

    /**
     * @return list<MenuItemInterface>
     */
    public function itemsFor(AdminUser $user): array
    {
        return array_values(array_filter(
            $this->loader->load(),
            static function (MenuItemInterface $item) use ($user): bool {
                $permission = $item->getPermission();

                return $permission === '' || $user->can($permission);
            },
        ));
    }

    /** @return list<AdminSectionInterface> */
    public function sectionsFor(AdminUser $user): array
    {
        return $this->sections->build($this->itemsFor($user));
    }
}
