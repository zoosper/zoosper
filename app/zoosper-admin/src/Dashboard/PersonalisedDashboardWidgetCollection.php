<?php

declare(strict_types=1);

namespace Zoosper\Admin\Dashboard;

use Zoosper\AdminDashboard\DashboardWidget;

final readonly class PersonalisedDashboardWidgetCollection
{
    /**
     * @param list<DashboardWidget> $availableWidgets
     * @param list<DashboardWidget> $visibleWidgets
     * @param list<string> $hiddenWidgetCodes
     */
    public function __construct(
        public array $availableWidgets,
        public array $visibleWidgets,
        public array $hiddenWidgetCodes,
        public int $failureCount = 0,
        public bool $customised = false,
    ) {
    }
}
