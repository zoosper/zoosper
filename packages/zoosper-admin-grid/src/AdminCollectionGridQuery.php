<?php

declare(strict_types=1);

namespace Zoosper\AdminGrid;

use Zoosper\Core\Http\Request;
use Zoosper\Grid\GridDefinition;

/** Adapts flat Admin Grid GET controls to canonical persisted Grid state. */
final class AdminCollectionGridQuery
{
    /** @return array<string, mixed> */
    public static function values(Request $request, GridDefinition $definition): array
    {
        $parameters = $request->queryParams();
        $state = [];

        foreach (['page', 'page_size'] as $key) {
            if (array_key_exists($key, $parameters) && !is_array($parameters[$key])) {
                $state[$key] = (string) $parameters[$key];
            }
        }

        if (array_key_exists('sort', $parameters) && !is_array($parameters['sort'])) {
            $state['sort_by'] = (string) $parameters['sort'];
        }
        if (array_key_exists('dir', $parameters) && !is_array($parameters['dir'])) {
            $state['sort_dir'] = (string) $parameters['dir'];
        }

        $filters = [];
        foreach ($definition->filterKeys() as $key) {
            if (!array_key_exists($key, $parameters)) {
                continue;
            }
            $filters[$key] = is_array($parameters[$key])
                ? $request->queryList($key)
                : (string) $parameters[$key];
        }
        if ($filters !== []) {
            $state['filters'] = $filters;
        }

        if (array_key_exists('visible_columns', $parameters)) {
            $state['visible_columns'] = $request->queryList('visible_columns');
        } elseif ($request->query('columns_submitted') === '1') {
            $state['visible_columns'] = [];
        }

        if (array_key_exists('column_order', $parameters)) {
            $state['column_order'] = $request->queryList('column_order');
        }

        return $state;
    }

    public static function bookmark(Request $request): ?int
    {
        $value = $request->query('bookmark');

        return is_string($value) && preg_match('/^[1-9][0-9]*$/', $value) === 1
            ? (int) $value
            : null;
    }

    private function __construct()
    {
    }
}











