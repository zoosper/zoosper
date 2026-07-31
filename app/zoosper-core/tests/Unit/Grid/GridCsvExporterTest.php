<?php

declare(strict_types=1);

namespace Zoosper\Core\Tests\Unit\Grid;

use Zoosper\Core\Grid\GridColumn;
use Zoosper\Core\Grid\GridCsvExporter;
use Zoosper\Core\Grid\GridDefinition;

function exportDefinition(): GridDefinition
{
    return new GridDefinition('Pages', [
        new GridColumn('id', 'ID', toggleable: false),
        new GridColumn('title', 'Title'),
        new GridColumn('status', 'Status', defaultVisible: false),
        new GridColumn('actions', 'Actions', render: static fn (): string => 'Edit'),
    ]);
}

test('CSV export follows selected visible columns and excludes actions', function (): void {
    $csv = (new GridCsvExporter())->export(
        exportDefinition(),
        [['id' => 7, 'title' => 'Hello, world', 'status' => 'draft']],
        ['id', 'title', 'actions'],
    );

    expect($csv)->toBe("ID,Title\n7,\"Hello, world\"\n");
    expect($csv)->not->toContain('Actions');
});

test('CSV export uses default-visible columns when no selection is supplied', function (): void {
    $csv = (new GridCsvExporter())->export(
        exportDefinition(),
        [['id' => 7, 'title' => '=2+2', 'status' => 'draft']],
    );

    expect($csv)->toContain('ID,Title');
    expect($csv)->not->toContain('Status');
    expect($csv)->not->toContain('Actions');
});
