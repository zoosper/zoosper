<?php

declare(strict_types=1);

namespace Zoosper\ApiGrid\Page;

use Zoosper\ApiGrid\Definition\ApiGridDefinition;
use Zoosper\Grid\DataSource\GridDataSourceCapabilities;
use Zoosper\Grid\DataSource\GridQuery;

/** Converts untrusted request values into a capability-constrained Grid query. */
final class ApiGridQueryFactory
{
    /** @param array<string, mixed> $values */
    public function fromValues(
        array $values,
        ApiGridDefinition $definition,
        GridDataSourceCapabilities $capabilities,
    ): GridQuery {
        $page = max(1, filter_var($values['page'] ?? 1, FILTER_VALIDATE_INT) ?: 1);
        $requestedSize = filter_var($values['page_size'] ?? $definition->pageSizes[0], FILTER_VALIDATE_INT);
        $pageSize = in_array($requestedSize, $definition->pageSizes, true)
            ? (int) $requestedSize
            : $definition->pageSizes[0];

        $sort = is_string($values['sort'] ?? null) ? trim($values['sort']) : null;
        if ($sort === '' || !$capabilities->supportsSort((string) $sort)) {
            $sort = null;
        }
        $direction = ($values['dir'] ?? 'asc') === 'desc' ? 'desc' : 'asc';

        $filters = [];
        $providedFilters = is_array($values['filters'] ?? null) ? $values['filters'] : [];
        foreach ($providedFilters as $key => $value) {
            if (is_string($key) && $capabilities->supportsFilter($key)) {
                $filters[$key] = $value;
            }
        }

        $search = null;
        if ($capabilities->searchable && is_string($values['q'] ?? null)) {
            $candidate = trim($values['q']);
            $search = $candidate !== '' ? $candidate : null;
        }

        $cursor = is_string($values['cursor'] ?? null) && $values['cursor'] !== ''
            ? $values['cursor']
            : null;

        return new GridQuery($page, $pageSize, $sort, $direction, $filters, $search, $cursor);
    }
}
