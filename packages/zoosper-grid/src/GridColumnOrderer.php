<?php

declare(strict_types=1);

namespace Zoosper\Grid;

/**
 * Reorders a GridDefinition from persisted user state without allowing unknown
 * keys to create columns or mandatory columns to disappear.
 */
final readonly class GridColumnOrderer
{
    /** @param list<string> $orderedKeys */
    public function apply(GridDefinition $definition, array $orderedKeys): GridDefinition
    {
        $columnsByKey = [];
        foreach ($definition->columns as $column) {
            $columnsByKey[$column->key] = $column;
        }

        $ordered = [];
        foreach (array_values(array_unique(array_map('strval', $orderedKeys))) as $key) {
            if (isset($columnsByKey[$key])) {
                $ordered[] = $columnsByKey[$key];
                unset($columnsByKey[$key]);
            }
        }

        foreach ($definition->columns as $column) {
            if (isset($columnsByKey[$column->key])) {
                $ordered[] = $column;
                unset($columnsByKey[$column->key]);
            }
        }

        return new GridDefinition(
            title: $definition->title,
            columns: $ordered,
            filters: $definition->filters,
            defaultSort: $definition->defaultSort,
            defaultSortDir: $definition->defaultSortDir,
            emptyMessage: $definition->emptyMessage,
        );
    }
}











