<?php

declare(strict_types=1);

namespace Zoosper\Grid;

use Zoosper\Core\Module\ModuleRegistry;

/**
 * Discovers module-owned grid column/filter contributions and applies them to
 * a base GridDefinition.
 *
 * Phase B2 extensibility: mirrors the "a module is a folder you drop in"
 * philosophy already used for routes/services/controllers/admin menu/admin
 * assets — a module contributes to a grid it does not own by shipping a
 * config/grid_columns.php file, with NO change required to the grid's own
 * definition class or controller. This is the same pattern Magento's UI
 * Component DI-merge and WordPress's `manage_{screen}_columns` filter hook
 * both solve, expressed as a plain PHP config file per module (consistent
 * with every other Zoosper module config file) rather than XML or a global
 * hook system.
 *
 * Each module's config/grid_columns.php returns:
 *
 *   return [
 *       'login-history' => [
 *           'columns' => [ new GridColumn(...), ... ],
 *           'filters' => [ new GridFilter(...), ... ],
 *       ],
 *       // a module may contribute to multiple grids in one file
 *   ];
 *
 * Discovery deliberately depends ONLY on ModuleRegistry (path + enabled
 * modules), not on ModuleConfigAggregator/ServiceProviderLoader, so this class
 * has no hidden dependency on config-merging internals that were not
 * available when this was built.
 */
final class GridColumnRegistry
{
    /**
     * @var array<string, array{columns: list<GridColumn>, filters: list<GridFilter>}>|null
     */
    private ?array $cachedContributions = null;

    public function __construct(private readonly ModuleRegistry $modules)
    {
    }

    /**
     * Apply any module-contributed columns/filters for the given grid key to
     * the base definition, returning a NEW GridDefinition. Returns the base
     * definition UNCHANGED when no module contributes to this grid key.
     */
    public function apply(string $gridKey, GridDefinition $definition): GridDefinition
    {
        $contributions = $this->discover();
        $forThisGrid = $contributions[$gridKey] ?? null;

        if ($forThisGrid === null) {
            return $definition;
        }

        return $definition
            ->withAdditionalColumns($forThisGrid['columns'])
            ->withAdditionalFilters($forThisGrid['filters']);
    }

    /**
     * Load and merge every enabled module's config/grid_columns.php, keyed by
     * grid key. Cached per instance for the lifetime of the request, mirroring
     * ModuleRegistry's own per-instance memoization (Phase 1.108) — discovery
     * is pure and module state cannot change mid-request.
     *
     * @return array<string, array{columns: list<GridColumn>, filters: list<GridFilter>}>
     */
    private function discover(): array
    {
        if ($this->cachedContributions !== null) {
            return $this->cachedContributions;
        }

        $merged = [];

        foreach ($this->modules->enabledModules() as $module) {
            $configFile = rtrim($module->path, '/\\') . '/config/grid_columns.php';
            if (!is_file($configFile)) {
                continue;
            }

            $contributed = require $configFile;
            if (!is_array($contributed)) {
                continue;
            }

            foreach ($contributed as $gridKey => $entry) {
                if (!is_string($gridKey) || !is_array($entry)) {
                    continue;
                }

                $columns = is_array($entry['columns'] ?? null) ? $entry['columns'] : [];
                $filters = is_array($entry['filters'] ?? null) ? $entry['filters'] : [];

                if (!isset($merged[$gridKey])) {
                    $merged[$gridKey] = ['columns' => [], 'filters' => []];
                }

                foreach ($columns as $column) {
                    if ($column instanceof GridColumn) {
                        $merged[$gridKey]['columns'][] = $column;
                    }
                }
                foreach ($filters as $filter) {
                    if ($filter instanceof GridFilter) {
                        $merged[$gridKey]['filters'][] = $filter;
                    }
                }
            }
        }

        $this->cachedContributions = $merged;

        return $this->cachedContributions;
    }
}

