<?php

declare(strict_types=1);

namespace Zoosper\Admin\Dashboard;

use Zoosper\AdminDashboard\DashboardWidget;

final readonly class DashboardWidgetCollection
{
    /** @param list<DashboardWidget> $widgets */
    public function __construct(public array $widgets, public int $failureCount = 0)
    {
    }
}
