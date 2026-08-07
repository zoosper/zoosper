<?php

declare(strict_types=1);

namespace Zoosper\Page\Admin;

use Zoosper\AdminGrid\GridCompactWorkspaceRenderer;
use Zoosper\AdminGrid\GridViewState;
use Zoosper\AdminGrid\GridViewStateResolver;
use Zoosper\AdminGrid\GridWorkspaceRenderer;
use Zoosper\Grid\GridColumnOrderer;
use Zoosper\Core\Url\AdminUrlGenerator;

/** Pages-specific integration seam for the shared Admin Grid workspace. */
final readonly class PageGridWorkspace
{
    public const GRID_KEY = 'admin.pages';
    public const ACTION = '/admin/pages';

    public function __construct(
        private PageGridDefinition $definition,
        private GridViewStateResolver $resolver,
        private GridWorkspaceRenderer|GridCompactWorkspaceRenderer $renderer,
        private ?GridColumnOrderer $columnOrderer = null,
        private ?AdminUrlGenerator $adminUrls = null,
    ) {
    }

    /**
     * @param array<string, mixed> $queryState
     * @return array{state: GridViewState, html: string}
     */
    public function resolve(
        int $adminUserId,
        array $queryState,
        ?int $bookmarkId = null,
    ): array {
        $state = $this->resolver->resolve(
            adminUserId: $adminUserId,
            gridKey: self::GRID_KEY,
            definition: $this->definition->build(),
            queryState: $queryState,
            bookmarkId: $bookmarkId,
        );

        // Explicit GET column state must win immediately. This is separate from
        // persisted preferences: Apply columns is a read-only preview/update of
        // the current URL, while Save view persists the resulting workspace.
        if (array_key_exists('visible_columns', $queryState)) {
            $state = $this->withExplicitColumns($state, $queryState);
        }

        // The resolved definition is intentionally visibility-filtered for the
        // table. The Columns chooser must instead receive the complete ordered
        // definition, otherwise a hidden column disappears from the chooser and
        // can never be selected again.
        $workspaceDefinition = ($this->columnOrderer ?? new GridColumnOrderer())
            ->apply($this->definition->build(), $state->columnOrder);
        $workspaceState = new GridViewState(
            definition: $workspaceDefinition,
            criteria: $state->criteria,
            visibleColumns: $state->visibleColumns,
            bookmarks: $state->bookmarks,
            activeBookmarkId: $state->activeBookmarkId,
            columnOrder: $state->columnOrder,
        );

        return [
            // Keep the visibility-filtered state authoritative for table rows,
            // navigation, export and persistence.
            'state' => $state,
            // Render controls from the complete definition so hidden columns
            // remain available as unchecked choices.
            'html' => $this->renderer->render($workspaceState, $this->action()),
        ];
    }

    public function action(): string
    {
        return $this->adminUrls?->url('pages') ?? '/admin/pages';
    }

    /** @param array<string, mixed> $queryState */
    private function withExplicitColumns(GridViewState $state, array $queryState): GridViewState
    {
        // The resolver deliberately returns a visibility-filtered definition.
        // Never use that filtered definition to validate a new selection or a
        // hidden column becomes unknown and is removed from column_order.
        $completeDefinition = $this->definition->build();
        $known = $completeDefinition->allColumnKeys();
        $knownMap = array_fill_keys($known, true);

        $visible = $this->stringList($queryState['visible_columns'] ?? []);
        $visible = array_values(array_filter(
            $visible,
            static fn (string $key): bool => isset($knownMap[$key]),
        ));

        foreach ($completeDefinition->columns as $column) {
            if (!$column->toggleable && !in_array($column->key, $visible, true)) {
                $visible[] = $column->key;
            }
        }

        $order = $this->stringList($queryState['column_order'] ?? $state->columnOrder);
        $order = array_values(array_filter(
            $order,
            static fn (string $key): bool => isset($knownMap[$key]),
        ));
        foreach ($known as $key) {
            if (!in_array($key, $order, true)) {
                $order[] = $key;
            }
        }

        $ordered = ($this->columnOrderer ?? new GridColumnOrderer())
            ->apply($completeDefinition, $order);
        $definition = $ordered->withVisibleColumnKeys($visible);

        return new GridViewState(
            definition: $definition,
            criteria: $state->criteria,
            visibleColumns: $visible,
            bookmarks: $state->bookmarks,
            activeBookmarkId: $state->activeBookmarkId,
            columnOrder: $order,
        );
    }

    /** @return list<string> */
    private function stringList(mixed $value): array
    {
        if (!is_array($value)) {
            return [];
        }

        $result = [];
        foreach ($value as $item) {
            $key = trim((string) $item);
            if ($key !== '' && !in_array($key, $result, true)) {
                $result[] = $key;
            }
        }

        return $result;
    }
}
