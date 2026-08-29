<?php

declare(strict_types=1);

namespace Zoosper\Auth\Dashboard;

use Zoosper\AdminDashboard\Contract\DashboardWidgetContributorInterface;
use Zoosper\AdminDashboard\DashboardWidget;
use Zoosper\Auth\Repository\AdminUserRepository;

final readonly class AdminUserCountDashboardWidgetContributor implements DashboardWidgetContributorInterface
{
    public function __construct(private AdminUserRepository $users)
    {
    }

    public function widgets(): iterable
    {
        yield new DashboardWidget(
            code: 'auth.active-admin-users',
            title: 'Active admin users',
            value: (string) $this->users->countByStatus('active'),
            description: 'Accounts currently permitted to sign in.',
            sortOrder: 20,
        );
    }
}
