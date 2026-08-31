<?php

declare(strict_types=1);

namespace Zoosper\Auth\Lifecycle;

final readonly class AdminUserLifecycleResult
{
    private function __construct(public bool $successful, public string $message)
    {
    }

    public static function success(string $message): self
    {
        return new self(true, $message);
    }

    public static function denied(string $message): self
    {
        return new self(false, $message);
    }
}










