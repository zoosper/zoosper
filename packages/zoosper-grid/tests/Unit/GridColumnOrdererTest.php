<?php

declare(strict_types=1);

namespace Zoosper\Grid\Tests\Unit;

use Zoosper\Grid\GridColumn;
use Zoosper\Grid\GridColumnOrderer;
use Zoosper\Grid\GridDefinition;

test('persisted column order is applied and unspecified columns remain stable', function (): void {
    $definition = new GridDefinition('Pages', [
        new GridColumn('id', 'ID', toggleable: false),
        new GridColumn('title', 'Title'),
        new GridColumn('status', 'Status'),
        new GridColumn('actions', 'Actions', toggleable: false),
    ]);

    $ordered = (new GridColumnOrderer())->apply($definition, ['status', 'title']);

    expect($ordered->allColumnKeys())->toBe(['status', 'title', 'id', 'actions']);
});

test('unknown and duplicate persisted keys are ignored', function (): void {
    $definition = new GridDefinition('Pages', [
        new GridColumn('id', 'ID'),
        new GridColumn('title', 'Title'),
        new GridColumn('actions', 'Actions'),
    ]);

    $ordered = (new GridColumnOrderer())->apply(
        $definition,
        ['title', 'retired_column', 'title'],
    );

    expect($ordered->allColumnKeys())->toBe(['title', 'id', 'actions']);
});











