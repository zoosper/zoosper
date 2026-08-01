<?php

declare(strict_types=1);

namespace Zoosper\Auth\Admin\Grid;

use Zoosper\Grid\GridColumn;
use Zoosper\Grid\GridColumnRegistry;
use Zoosper\Grid\GridDefinition;
use Zoosper\Grid\GridFilter;

/** Defines the shared Grid contract for the Roles listing. */
final readonly class RoleGridDefinition
{
    public const KEY = 'admin.roles';

    public function __construct(private ?GridColumnRegistry $columns = null)
    {
    }

    public function build(): GridDefinition
    {
        $definition = new GridDefinition(
            title: 'Roles & Permissions',
            columns: [
                new GridColumn('id', 'ID', sortable: true, align: 'right', toggleable: false),
                new GridColumn('label', 'Label', sortable: true),
                new GridColumn('code', 'Code', sortable: true),
                new GridColumn(
                    key: 'actions',
                    label: 'Actions',
                    toggleable: false,
                    render: static function (mixed $value, array $row): string {
                        $id = (int) ($row['id'] ?? 0);

                        return '<a href="/admin/roles/edit?id=' . $id . '">Edit</a>';
                    },
                ),
            ],
            filters: [
                new GridFilter('q', 'Search'),
            ],
            defaultSort: 'id',
            defaultSortDir: 'desc',
            emptyMessage: 'No roles found.',
        );

        return $this->columns?->apply(self::KEY, $definition) ?? $definition;
    }
}
