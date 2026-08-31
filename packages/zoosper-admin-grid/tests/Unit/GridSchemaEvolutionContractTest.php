<?php

declare(strict_types=1);

namespace Zoosper\AdminGrid\Tests\Unit;

use Zoosper\AdminGrid\GridStateNormaliser;
use Zoosper\Grid\GridColumn;
use Zoosper\Grid\GridColumnOrderer;
use Zoosper\Grid\GridDefinition;

function evolvedGridDefinition(): GridDefinition
{
    return new GridDefinition(
        title: 'Evolved Grid',
        columns: [
            new GridColumn('id', 'ID', toggleable: false),
            new GridColumn('name', 'Name'),
            new GridColumn('email', 'Email'),
            new GridColumn('department', 'Department'),
            new GridColumn('actions', 'Actions', toggleable: false),
        ],
    );
}

it('appends newly declared columns when applying an older saved order', function (): void {
    $definition = (new GridColumnOrderer())->apply(
        evolvedGridDefinition(),
        ['id', 'email', 'name', 'actions'],
    );

    expect($definition->allColumnKeys())->toBe([
        'id',
        'email',
        'name',
        'actions',
        'department',
    ]);
});

it('keeps mandatory ID and Actions visible for older saved visibility state', function (): void {
    $state = (new GridStateNormaliser())->normalise([
        'visible_columns' => ['name', 'retired_column'],
    ], evolvedGridDefinition());

    expect($state['visible_columns'])->toContain('id')
        ->toContain('name')
        ->toContain('actions')
        ->not->toContain('retired_column');
});

it('documents stable bookmark visibility for newly introduced optional columns', function (): void {
    $state = (new GridStateNormaliser())->normalise([
        'visible_columns' => ['id', 'name', 'actions'],
    ], evolvedGridDefinition());

    expect($state['visible_columns'])->toBe(['id', 'name', 'actions'])
        ->not->toContain('department');
});











