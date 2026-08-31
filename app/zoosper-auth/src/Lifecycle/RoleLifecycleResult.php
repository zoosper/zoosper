<?php

declare(strict_types=1);

namespace Zoosper\Auth\Lifecycle;

final readonly class RoleLifecycleResult
{
    private function __construct(public bool $successful, public string $message, public int $assignmentCount = 0)
    {
    }

    public static function success(string $message): self
    {
        return new self(true, $message);
    }

    public static function denied(string $message, int $assignmentCount = 0): self
    {
        return new self(false, $message, $assignmentCount);
    }
}










