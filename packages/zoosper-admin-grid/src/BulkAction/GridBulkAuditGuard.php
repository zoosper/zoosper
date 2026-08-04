<?php

declare(strict_types=1);

namespace Zoosper\AdminGrid\BulkAction;

use InvalidArgumentException;
use Zoosper\Grid\BulkAction\GridBulkActionDefinition;
use Zoosper\Grid\BulkAction\GridBulkSelection;

/** Callable fail-closed adapter that verifies audit infrastructure readiness. */
final readonly class GridBulkAuditGuard implements GridBulkAuditGuardInterface
{
    /** @param callable(GridBulkActionDefinition, GridBulkSelection): bool $isAvailable */
    public function __construct(private mixed $isAvailable)
    {
        if (!is_callable($isAvailable)) {
            throw new InvalidArgumentException('Grid bulk audit readiness check must be callable.');
        }
    }

    public function assertAvailable(
        GridBulkActionDefinition $definition,
        GridBulkSelection $selection,
    ): void {
        if (!($this->isAvailable)($definition, $selection)) {
            throw new InvalidArgumentException(
                sprintf('Audit infrastructure is unavailable for Grid bulk action "%s".', $definition->id),
            );
        }
    }
}
