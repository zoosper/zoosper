<?php

declare(strict_types=1);

namespace Zoosper\Auth\Admin\Grid;

use Zoosper\Grid\GridColumn;
use Zoosper\Grid\GridColumnRegistry;
use Zoosper\Grid\GridDefinition;
use Zoosper\Grid\GridFilter;
use Zoosper\Core\Url\AdminUrlGenerator;

/** Defines the shared Grid contract for the Roles listing. */
final readonly class RoleGridDefinition
{
    public const KEY = 'admin.roles';

    public function __construct(
        private ?GridColumnRegistry $columns = null,
        private ?AdminUrlGenerator $adminUrls = null,
    )
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
                    render: function (mixed $value, array $row): string {
                        $id = (int) ($row['id'] ?? 0);

                        $url = $this->adminUrls?->url('roles/edit', ['id' => $id]) ?? '/admin/roles/edit?id=' . $id;

                        return '<a href="' . htmlspecialchars($url, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '">Edit</a>';
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










