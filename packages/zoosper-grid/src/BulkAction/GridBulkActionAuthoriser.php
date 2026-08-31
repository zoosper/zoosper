<?php

declare(strict_types=1);

namespace Zoosper\Grid\BulkAction;

/** Filters registered actions before any definition reaches browser markup. */
final readonly class GridBulkActionAuthoriser
{
    /**
     * @param list<GridBulkActionDefinition> $definitions
     * @param callable(string): bool $isAllowed
     * @return list<GridBulkActionDefinition>
     */
    public function authorised(array $definitions, callable $isAllowed): array
    {
        return array_values(array_filter(
            $definitions,
            static fn (GridBulkActionDefinition $definition): bool =>
                $definition->requiredPermission === null
                || $isAllowed($definition->requiredPermission),
        ));
    }
}











