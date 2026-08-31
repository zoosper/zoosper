<?php

declare(strict_types=1);

namespace Zoosper\AdminGrid\Tests\Unit;

use Zoosper\AdminGrid\GridStateNormaliser;
use Zoosper\Grid\GridColumn;
use Zoosper\Grid\GridDefinition;

test('column order is normalised for bookmark persistence', function (): void {
    $definition = new GridDefinition('Pages', [
        new GridColumn('id', 'ID'),
        new GridColumn('title', 'Title'),
        new GridColumn('status', 'Status'),
        new GridColumn('actions', 'Actions'),
    ]);

    $state = (new GridStateNormaliser())->normalise([
        'column_order' => ['status', 'title', 'retired', 'status'],
    ], $definition);

    expect($state['column_order'])->toBe(['status', 'title', 'id', 'actions']);
});











