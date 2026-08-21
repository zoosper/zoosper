<?php

declare(strict_types=1);

namespace Zoosper\AdminGrid\Tests\Unit;

use Zoosper\AdminGrid\GridViewState;
use Zoosper\AdminGrid\GridWorkspaceAuditedCsvExportService;
use Zoosper\AdminGrid\GridWorkspaceCsvExportService;
use Zoosper\AdminGrid\GridWorkspaceExportAudit;
use Zoosper\AdminGrid\GridWorkspaceExportAuditorInterface;
use Zoosper\Pagination\Pager;
use Zoosper\Grid\GridColumn;
use Zoosper\Grid\GridCriteria;
use Zoosper\Grid\GridCsvExporter;
use Zoosper\Grid\GridDefinition;

test('completed export records authenticated identity and bounded result', function (): void {
    $auditor = new class implements GridWorkspaceExportAuditorInterface {
        public ?GridWorkspaceExportAudit $recorded = null;
        public function record(GridWorkspaceExportAudit $audit): void
        {
            $this->recorded = $audit;
        }
    };
    $state = new GridViewState(
        definition: new GridDefinition('Pages', [new GridColumn('title', 'Title')]),
        criteria: new GridCriteria(new Pager(1, 20), 'title', 'asc', ['status' => 'published']),
        visibleColumns: ['title'],
        columnOrder: ['title'],
        bookmarks: [],
    );
    $result = (new GridWorkspaceAuditedCsvExportService(
        new GridWorkspaceCsvExportService(new GridCsvExporter()),
        $auditor,
    ))->export(10, 'admin.pages', $state, [['title' => 'Landing']], 'pages.csv');

    expect($result->exportedRows)->toBe(1);
    expect($auditor->recorded)->not->toBeNull();
    expect($auditor->recorded->adminUserId)->toBe(10);
    expect($auditor->recorded->gridKey)->toBe('admin.pages');
    expect($auditor->recorded->filters)->toBe(['status' => 'published']);
    expect($auditor->recorded->visibleColumns)->toBe(['title']);
});
