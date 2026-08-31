<?php

declare(strict_types=1);

namespace Zoosper\AdminGrid\Tests\Unit;

use Zoosper\AdminGrid\GridViewState;
use Zoosper\AdminGrid\GridWorkspaceCsvExportService;
use Zoosper\AdminGrid\GridWorkspaceExportPolicy;
use Zoosper\Pagination\Pager;
use Zoosper\Grid\GridColumn;
use Zoosper\Grid\GridCriteria;
use Zoosper\Grid\GridCsvExporter;
use Zoosper\Grid\GridDefinition;

function exportState(): GridViewState
{
    return new GridViewState(
        definition: new GridDefinition('Pages', [
            new GridColumn('title', 'Title'),
            new GridColumn('status', 'Status'),
            new GridColumn('actions', 'Actions', toggleable: false),
        ]),
        criteria: new GridCriteria(new Pager(1, 20), 'title', 'asc'),
        visibleColumns: ['title', 'actions'],
        columnOrder: ['title', 'status', 'actions'],
        bookmarks: [],
    );
}

test('CSV export follows resolved visible columns and excludes Actions', function (): void {
    $result = (new GridWorkspaceCsvExportService(new GridCsvExporter()))->export(
        exportState(),
        [['title' => 'Landing', 'status' => 'published', 'actions' => '<a>Edit</a>']],
        'Pages Export',
    );

    expect($result->filename)->toBe('pages-export.csv');
    expect($result->csv)->toContain("Title\n")
        ->toContain("Landing\n")
        ->not->toContain('Status')
        ->not->toContain('Edit');
    expect($result->exportedRows)->toBe(1);
    expect($result->truncated)->toBeFalse();
});

test('CSV export applies a hard row ceiling and reports truncation', function (): void {
    $service = new GridWorkspaceCsvExportService(
        new GridCsvExporter(),
        new GridWorkspaceExportPolicy(2),
    );
    $result = $service->export(exportState(), [
        ['title' => 'One'],
        ['title' => 'Two'],
        ['title' => 'Three'],
    ], 'pages.csv');

    expect($result->exportedRows)->toBe(2);
    expect($result->truncated)->toBeTrue();
    expect($result->csv)->not->toContain('Three');
});

test('CSV response headers prevent sniffing and shared caching', function (): void {
    $result = (new GridWorkspaceCsvExportService(new GridCsvExporter()))
        ->export(exportState(), [], 'pages');

    expect($result->headers())->toBe([
        'Content-Type' => 'text/csv; charset=UTF-8',
        'Content-Disposition' => 'attachment; filename="pages.csv"',
        'X-Content-Type-Options' => 'nosniff',
        'Cache-Control' => 'private, no-store',
    ]);
});











