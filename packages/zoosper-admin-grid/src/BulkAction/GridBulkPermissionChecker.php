<?php

declare(strict_types=1);

namespace Zoosper\AdminGrid\BulkAction;

use InvalidArgumentException;

/** Callable adapter for the authenticated administrator's permission check. */
final readonly class GridBulkPermissionChecker implements GridBulkPermissionCheckerInterface
{
    /** @param callable(string): bool $checker */
    public function __construct(private mixed $checker)
    {
        if (!is_callable($checker)) {
            throw new InvalidArgumentException('Grid bulk permission checker must be callable.');
        }
    }

    public function isAllowed(string $permission): bool
    {
        return $permission !== '' && (bool) ($this->checker)($permission);
    }
}
