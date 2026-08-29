<?php

declare(strict_types=1);

namespace Zoosper\Admin\Dashboard;

final readonly class DashboardPreference
{
    /**
     * @param list<string> $hiddenWidgetCodes
     * @param list<string> $widgetOrder
     */
    public function __construct(
        public array $hiddenWidgetCodes,
        public array $widgetOrder,
    ) {
    }
}
