<?php

declare(strict_types=1);

namespace Zoosper\AdminDashboard\Contract;

use Zoosper\AdminDashboard\DashboardRole;
use Zoosper\AdminDashboard\DashboardRolePreference;

interface DashboardRolePreferenceRepositoryInterface
{
    /** @return list<DashboardRole> */
    public function roles(): array;

    public function findForRole(int $roleId): ?DashboardRolePreference;

    /** @return list<DashboardRolePreference> Deterministically ordered by role code. */
    public function findForUser(int $adminUserId): array;

    /**
     * @param list<string> $hiddenWidgetCodes
     * @param list<string> $widgetOrder
     */
    public function saveForRole(int $roleId, array $hiddenWidgetCodes, array $widgetOrder): void;

    public function clearForRole(int $roleId): void;
}











