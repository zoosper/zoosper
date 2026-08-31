<?php

declare(strict_types=1);

namespace Zoosper\Grid\BulkAction;

/** Framework-neutral result returned by a feature-owned executor. */
final readonly class GridBulkActionExecutionResult
{
    /** @param array<string, int|string|bool|null> $context */
    private function __construct(
        public bool $successful,
        public string $message,
        public array $context = [],
    ) {
    }

    /** @param array<string, int|string|bool|null> $context */
    public static function success(string $message, array $context = []): self
    {
        return new self(true, $message, $context);
    }

    /** @param array<string, int|string|bool|null> $context */
    public static function failure(string $message, array $context = []): self
    {
        return new self(false, $message, $context);
    }
}











