<?php

declare(strict_types=1);

namespace Zoosper\AdminDashboard\Contract;

use Zoosper\AdminDashboard\DashboardWidget;

interface DashboardWidgetContributorInterface
{
    /** @return iterable<DashboardWidget> */
    public function widgets(): iterable;
}
