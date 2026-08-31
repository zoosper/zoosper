<?php

declare(strict_types=1);

namespace Zoosper\AdminDashboard;

use InvalidArgumentException;

final readonly class DashboardWidget
{
    public string $code;
    public string $title;
    public string $value;
    public string $description;

    public function __construct(
        string $code,
        string $title,
        string $value,
        string $description,
        public int $sortOrder = 100,
    ) {
        $this->code = trim($code);
        $this->title = trim($title);
        $this->value = trim($value);
        $this->description = trim($description);

        if ($this->code === '' || $this->title === '' || $this->value === '' || $this->description === '') {
            throw new InvalidArgumentException('Dashboard widget code, title, value and description must not be empty.');
        }

        if (preg_match('/^[a-z0-9][a-z0-9.-]*$/', $this->code) !== 1) {
            throw new InvalidArgumentException('Dashboard widget code must use lowercase letters, numbers, dots and hyphens.');
        }
    }
}











