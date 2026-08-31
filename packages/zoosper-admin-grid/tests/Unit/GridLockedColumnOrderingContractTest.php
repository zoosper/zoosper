<?php

declare(strict_types=1);

namespace Zoosper\AdminGrid\Tests\Unit;

use Zoosper\Grid\GridColumn;
use Zoosper\Grid\GridColumnOrderer;
use Zoosper\Grid\GridDefinition;

it('keeps ID first and Actions last while reordering configurable columns', function (): void {
    $definition = new GridDefinition('Users', [
        new GridColumn('id', 'ID', toggleable: false),
        new GridColumn('name', 'Name'),
        new GridColumn('email', 'Email'),
        new GridColumn('status', 'Status'),
        new GridColumn('actions', 'Actions', toggleable: false),
    ]);

    $ordered = (new GridColumnOrderer())->apply(
        $definition,
        ['id', 'status', 'email', 'name', 'actions'],
    );

    expect($ordered->allColumnKeys())->toBe([
        'id', 'status', 'email', 'name', 'actions',
    ]);
});











