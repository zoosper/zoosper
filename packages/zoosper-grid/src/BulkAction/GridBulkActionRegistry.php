<?php

declare(strict_types=1);

namespace Zoosper\Grid\BulkAction;

use InvalidArgumentException;

/** Shared registry. Feature modules contribute definitions, never Grid mechanics. */
final class GridBulkActionRegistry
{
    /** @var array<string, array<string, GridBulkActionDefinition>> */
    private array $definitions = [];

    public function register(string $gridKey, GridBulkActionDefinition $definition): void
    {
        $gridKey = trim($gridKey);
        if ($gridKey === '') {
            throw new InvalidArgumentException('Grid key cannot be empty.');
        }
        if (isset($this->definitions[$gridKey][$definition->id])) {
            throw new InvalidArgumentException(
                sprintf('Grid bulk action "%s" is already registered for "%s".', $definition->id, $gridKey),
            );
        }
        $this->definitions[$gridKey][$definition->id] = $definition;
    }

    /** @return list<GridBulkActionDefinition> */
    public function allForGrid(string $gridKey): array
    {
        return array_values($this->definitions[$gridKey] ?? []);
    }

    public function require(string $gridKey, string $actionId): GridBulkActionDefinition
    {
        return $this->definitions[$gridKey][$actionId]
            ?? throw new InvalidArgumentException(
                sprintf('Unknown Grid bulk action "%s" for "%s".', $actionId, $gridKey),
            );
    }
}
