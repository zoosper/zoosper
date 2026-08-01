<?php

declare(strict_types=1);

namespace Zoosper\AdminGrid;

/**
 * Produces a deterministic fingerprint for the persistent part of Grid state.
 * Page number is deliberately excluded because navigation does not dirty a view.
 */
final readonly class GridWorkspaceStateFingerprint
{
    /** @param array<string, mixed> $state */
    public function fromArray(array $state): string
    {
        $persistent = [
            'filters' => $state['filters'] ?? [],
            'sort_by' => $state['sort_by'] ?? null,
            'sort_dir' => $state['sort_dir'] ?? 'desc',
            'page_size' => (int) ($state['page_size'] ?? 20),
            'visible_columns' => array_values($state['visible_columns'] ?? []),
            'column_order' => array_values($state['column_order'] ?? []),
        ];
        $persistent['filters'] = $this->normaliseMap(
            is_array($persistent['filters']) ? $persistent['filters'] : [],
        );

        return hash('sha256', json_encode(
            $persistent,
            JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
        ));
    }

    public function fromViewState(GridViewState $state): string
    {
        return $this->fromArray([
            'filters' => $state->criteria->filters,
            'sort_by' => $state->criteria->sortBy,
            'sort_dir' => $state->criteria->sortDir,
            'page_size' => $state->criteria->pager->pageSize,
            'visible_columns' => $state->visibleColumns,
            'column_order' => $state->columnOrder,
        ]);
    }

    /** @param array<string, mixed> $values @return array<string, mixed> */
    private function normaliseMap(array $values): array
    {
        ksort($values);
        foreach ($values as &$value) {
            if (is_array($value)) {
                $value = array_values($value);
            }
        }
        unset($value);

        return $values;
    }
}
