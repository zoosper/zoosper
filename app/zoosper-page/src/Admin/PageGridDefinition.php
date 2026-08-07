<?php

declare(strict_types=1);

namespace Zoosper\Page\Admin;

use Zoosper\Grid\GridColumn;
use Zoosper\Grid\GridColumnRegistry;
use Zoosper\Grid\GridDefinition;
use Zoosper\Grid\GridFilter;
use Zoosper\Core\Url\AdminUrlGenerator;

final readonly class PageGridDefinition
{
    public const KEY='admin.pages';
    public function __construct(
        private ?GridColumnRegistry $columns = null,
        private ?PageGridSiteFilter $siteFilter = null,
        private ?AdminUrlGenerator $adminUrls = null,
    ) {}
    public function build():GridDefinition
    {
        $definition=new GridDefinition(
            title:'Pages',
            columns:[
                new GridColumn('id','ID',sortable:true,align:'right',toggleable:false),
                new GridColumn('title','Title',sortable:true),
                new GridColumn('slug','Slug',sortable:true),
                new GridColumn('status','Status',sortable:true),
                new GridColumn('site_name','Site'),
                new GridColumn('actions', 'Actions', toggleable: false, render: function (mixed $value, array $row): string {
                    $id = (int) ($row['id'] ?? 0);
                    $edit = $this->adminUrls?->url('pages/edit', ['id' => $id]) ?? '/admin/pages/edit?id=' . $id;
                    $preview = $this->adminUrls?->url('pages/preview', ['id' => $id]) ?? '/admin/pages/preview?id=' . $id;
                    return '<a href="' . htmlspecialchars($edit, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '">Edit</a> &nbsp;|&nbsp; <a href="' . htmlspecialchars($preview, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '" target="_blank" rel="noopener">Preview</a>';
                })
            ],
            filters:[
                new GridFilter('q','Search'),
                new GridFilter('title','Title'),
                new GridFilter('slug','Slug'),
                new GridFilter('status','Status','select',[
                    ['value'=>'','label'=>'All statuses'],['value'=>'draft','label'=>'Draft'],['value'=>'published','label'=>'Published'],
                ]),
                $this->siteFilter?->build()??new GridFilter('site_id','Site'),
            ],
            defaultSort:'id',defaultSortDir:'desc',emptyMessage:'No pages found.',
        );
        return $this->columns?->apply(self::KEY,$definition)??$definition;
    }
}
