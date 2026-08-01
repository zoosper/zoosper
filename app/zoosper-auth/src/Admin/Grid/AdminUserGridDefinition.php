<?php

declare(strict_types=1);

namespace Zoosper\Auth\Admin\Grid;

use Zoosper\Grid\GridColumn;
use Zoosper\Grid\GridColumnRegistry;
use Zoosper\Grid\GridDefinition;
use Zoosper\Grid\GridFilter;

/** Defines the shared Grid contract for the Admin Users listing. */
final readonly class AdminUserGridDefinition
{
    public const KEY = 'admin.users';

    public function __construct(private ?GridColumnRegistry $columns = null)
    {
    }

    public function build(): GridDefinition
    {
        $definition = new GridDefinition(
            title: 'Admin Users',
            columns: [
                new GridColumn('id', 'ID', sortable: true, align: 'right', toggleable: false),
                new GridColumn('name', 'Name', sortable: true),
                new GridColumn('email', 'Email', sortable: true),
                new GridColumn('status', 'Status', sortable: true),
                new GridColumn(
                    key: 'actions',
                    label: 'Actions',
                    toggleable: false,
                    render: static function (mixed $value, array $row): string {
                        $id = (int) ($row['id'] ?? 0);

                        return '<a href="/admin/users/edit?id=' . $id . '">Edit</a>';
                    },
                ),
            ],
            filters: [
                new GridFilter('q', 'Search'),
                new GridFilter('status', 'Status', 'select', [
                    ['value' => '', 'label' => 'All statuses'],
                    ['value' => 'active', 'label' => 'Active'],
                    ['value' => 'inactive', 'label' => 'Inactive'],
                ]),
            ],
            defaultSort: 'id',
            defaultSortDir: 'desc',
            emptyMessage: 'No admin users found.',
        );

        return $this->columns?->apply(self::KEY, $definition) ?? $definition;
    }
}
