<?php

declare(strict_types=1);

namespace Zoosper\AdminDashboard;

use InvalidArgumentException;

final readonly class DashboardRole
{
    public function __construct(
        public int $id,
        public string $code,
        public string $label,
    ) {
        if ($id <= 0 || trim($code) === '' || trim($label) === '') {
            throw new InvalidArgumentException('Dashboard role identity is invalid.');
        }
    }
}
