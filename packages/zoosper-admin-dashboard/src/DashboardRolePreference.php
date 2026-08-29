<?php

declare(strict_types=1);

namespace Zoosper\AdminDashboard;

use InvalidArgumentException;

final readonly class DashboardRolePreference
{
    /**
     * @param list<string> $hiddenWidgetCodes
     * @param list<string> $widgetOrder
     */
    public function __construct(
        public int $roleId,
        public string $roleCode,
        public array $hiddenWidgetCodes,
        public array $widgetOrder,
    ) {
        if ($roleId <= 0 || trim($roleCode) === '') {
            throw new InvalidArgumentException('Dashboard role preference identity is invalid.');
        }

        $this->assertUniqueCodes($hiddenWidgetCodes);
        $this->assertUniqueCodes($widgetOrder);
    }

    /** @param list<string> $codes */
    private function assertUniqueCodes(array $codes): void
    {
        $seen = [];
        foreach ($codes as $code) {
            if (!is_string($code) || trim($code) === '' || isset($seen[$code])) {
                throw new InvalidArgumentException('Dashboard role preference widget codes are invalid.');
            }
            $seen[$code] = true;
        }
    }
}
