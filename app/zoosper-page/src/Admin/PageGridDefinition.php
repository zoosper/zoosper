<?php

declare(strict_types=1);

namespace Zoosper\Page\Admin;

use Zoosper\Grid\GridColumn;
use Zoosper\Grid\GridColumnRegistry;
use Zoosper\Grid\GridDefinition;
use Zoosper\Grid\GridFilter;

final readonly class PageGridDefinition
{
    public const KEY = 'admin.pages';

    public function __construct(private ?GridColumnRegistry $columns = null)
    {
    }

    public function build(): GridDefinition
    {
        $definition = new GridDefinition(
            title: 'Pages',
            columns: [
                new GridColumn('id', 'ID', sortable: true, align: 'right', toggleable: false),
                new GridColumn('title', 'Title', sortable: true, toggleable: false),
                new GridColumn('slug', 'Slug', sortable: true),
                new GridColumn('status', 'Status', sortable: true),
                new GridColumn('site_name', 'Site'),
                new GridColumn(
                    key: 'actions',
                    label: 'Actions',
                    toggleable: false,
                    render: static function (mixed $value, array $row): string {
                        $id = (int) ($row['id'] ?? 0);

                        return '<a href="/admin/pages/edit?id=' . $id . '">Edit</a>'
                            . ' &nbsp;|&nbsp; '
                            . '<a href="/admin/pages/preview?id=' . $id . '" target="_blank" rel="noopener">Preview</a>';
                    },
                ),
            ],
            filters: [
                new GridFilter('q', 'Search'),
                new GridFilter('status', 'Status', 'select', [
                    ['value' => '', 'label' => 'All statuses'],
                    ['value' => 'draft', 'label' => 'Draft'],
                    ['value' => 'published', 'label' => 'Published'],
                ]),
                new GridFilter('site_id', 'Site ID'),
            ],
            defaultSort: 'id',
            defaultSortDir: 'desc',
            emptyMessage: 'No pages found.',
        );

        return $this->columns?->apply(self::KEY, $definition) ?? $definition;
    }
}
